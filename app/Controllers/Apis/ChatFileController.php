<?php

namespace App\Controllers\Apis;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Chat file upload + secure serve.
 *
 * Endpoints:
 *   POST /chat/uploadFile         — multipart upload (images + PDFs only)
 *   GET  /chat/file/serve/(:num)  — permission-checked download (for restricted
 *                                    Diet / Workout / Prescription PDFs)
 *
 * Uploads are written to writable/uploads/chat_files/ so they are NOT directly
 * web-accessible. Non-restricted files are mirrored into public/uploads/chat_files/
 * to keep the Flutter client's existing URL scheme working. Restricted files
 * (Diet Plan / Workout Plan / Prescription) are kept writable-only and can only
 * be fetched through the serve endpoint after a permission check.
 */
class ChatFileController extends Controller
{
    /** allowed mime types: images + pdf only */
    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
    ];

    /** 10 MB cap — the mobile app enforces 1 MB but portal users may send
     *  larger diet plans / prescriptions, so we allow up to 10 MB here. */
    private const MAX_BYTES = 10 * 1024 * 1024;

    private const RESTRICTED_CATEGORIES = ['diet_plan', 'workout_plan', 'prescription'];

    public function upload()
    {
        $session = session();

        // Accept common field names so we stay compatible with the existing
        // Flutter client (which posts under "file").
        $file = $this->request->getFile('file')
             ?? $this->request->getFile('fileUpload')
             ?? $this->request->getFile('attachment');
        if (!$file || !$file->isValid()) {
            return $this->fail('No valid file received.');
        }

        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            return $this->fail('Only images and PDFs are allowed.');
        }
        if ($file->getSize() > self::MAX_BYTES) {
            return $this->fail('File exceeds the 10 MB limit.');
        }

        // category & client_id posted alongside the file
        $category = strtolower(trim((string) $this->request->getPost('category')));
        if (!in_array($category, array_merge(['general'], self::RESTRICTED_CATEGORIES), true)) {
            $category = 'general';
        }
        $clientId = (int) $this->request->getPost('client_id');

        // who is uploading — portal session OR authenticated client
        [$uploaderType, $uploaderId] = $this->resolveUploader($session);

        // If a client uploads without specifying client_id, default it to self.
        if ($clientId <= 0 && $uploaderType === 'client') {
            $clientId = (int) $uploaderId;
        }

        // Only a mentor may tag a file as restricted (Diet/Workout/Prescription).
        $restricted = 0;
        if (in_array($category, self::RESTRICTED_CATEGORIES, true)) {
            if ($uploaderType !== 'mentor' || $clientId <= 0) {
                // silently downgrade if anyone else tries to use these categories
                $category   = 'general';
                $restricted = 0;
            } else {
                $restricted = 1;
            }
        }

        // Permission: mentor can only upload to an associated client's thread
        // (only checked when a client_id was actually supplied — mentor uploads
        //  that don't target a specific client aren't possible from the portal).
        if ($uploaderType === 'mentor' && $clientId > 0 && !$this->mentorOwnsClient($uploaderId, $clientId)) {
            return $this->fail('This client is not associated with you.', 403);
        }

        $isImage = strpos($mime, 'image/') === 0;
        $extGuess = $isImage ? $file->guessExtension() : 'pdf';
        $stored = uniqid('chat_', true) . '.' . $extGuess;

        // Always write to writable/uploads/chat_files so restricted files never
        // sit on a publicly-readable path. For NON-restricted files we also
        // mirror into public/uploads/chat_files so the existing Flutter URL
        // pattern (baseUrl/uploads/chat_files/<name>) keeps working.
        $writablePath = WRITEPATH . 'uploads/chat_files';
        if (!is_dir($writablePath)) {
            @mkdir($writablePath, 0775, true);
        }
        $file->move($writablePath, $stored);

        $publicUrl = null;
        if (!$restricted) {
            $publicDir = FCPATH . 'uploads/chat_files';
            if (!is_dir($publicDir)) {
                @mkdir($publicDir, 0775, true);
            }
            @copy($writablePath . DIRECTORY_SEPARATOR . $stored, $publicDir . DIRECTORY_SEPARATOR . $stored);
            $publicUrl = base_url('uploads/chat_files/' . $stored);
        }

        // record — wrap so legacy callers without a chat_files table can't blow up
        $fileId = null;
        try {
            $db = \Config\Database::connect();
            $db->table('chat_files')->insert([
                'filename'      => $stored,
                'original_name' => $file->getClientName(),
                'mime_type'     => $mime,
                'size_bytes'    => $file->getSize(),
                'uploader_type' => $uploaderType ?: 'client',
                'uploader_id'   => $uploaderId,
                'client_id'     => $clientId,
                'category'      => $category,
                'restricted'    => $restricted,
            ]);
            $fileId = $db->insertID();
        } catch (\Throwable $e) {
            // If migration hasn't been run yet, still return a usable URL.
            log_message('warning', 'chat_files insert failed: ' . $e->getMessage());
        }

        // Restricted files are served through the permission-checked proxy.
        $serveUrl = ($restricted && $fileId)
            ? base_url('chat/file/serve/' . $fileId)
            : $publicUrl;

        return $this->response->setJSON([
            'status'      => true,
            'msg'         => 'File uploaded',
            'file_id'     => $fileId,
            'filename'    => $stored,
            'file_url'    => $serveUrl,
            'mime_type'   => $mime,
            'category'    => $category,
            'restricted'  => (bool) $restricted,
            'message_type'=> $isImage ? 'image' : 'pdf',
        ]);
    }

    /**
     * Serve a file, enforcing:
     *   - restricted files (Diet/Workout/Prescription) may only be accessed by
     *     the target client OR the mentor who uploaded OR an admin.
     *   - any other viewer gets 403.
     */
    public function serve(int $fileId)
    {
        $db = \Config\Database::connect();
        $row = $db->table('chat_files')->where('id', $fileId)->get()->getRow();
        if (!$row) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $session = session();
        [$viewerType, $viewerId] = $this->resolveUploader($session, true);

        $allowed = false;
        if (!$row->restricted) {
            $allowed = true;
        } elseif ($viewerType === 'admin') {
            $allowed = true;
        } elseif ($viewerType === 'mentor' && (int) $viewerId === (int) $row->uploader_id) {
            $allowed = true;
        } elseif ($viewerType === 'client' && (int) $viewerId === (int) $row->client_id) {
            $allowed = true;
        }

        if (!$allowed) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $path = WRITEPATH . 'uploads/chat_files/' . $row->filename;
        if (!is_file($path)) {
            // legacy / fallback — maybe it's still in public only
            $path = FCPATH . 'uploads/chat_files/' . $row->filename;
        }
        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File missing');
        }

        return $this->response
            ->setHeader('Content-Type', $row->mime_type ?: 'application/octet-stream')
            ->setHeader('Content-Disposition', 'inline; filename="' . ($row->original_name ?: $row->filename) . '"')
            ->setHeader('Cache-Control', 'private, max-age=60')
            ->setBody(file_get_contents($path));
    }

    // ----------------------------------------------------------------- helpers

    private function resolveUploader($session, bool $allowClient = true): array
    {
        $usertype = $session->get('usertype');
        if ($usertype === 'admin') {
            return ['admin', (int) $session->get('userid')];
        }
        if ($usertype === 'mentor') {
            return ['mentor', (int) $session->get('doctor_id')];
        }
        // client — identified by token header (same pattern as other /apis routes)
        if ($allowClient) {
            $clientId = (int) ($this->request->getHeaderLine('X-Client-Id')
                ?: $this->request->getPost('auth_client_id'));
            if ($clientId > 0) {
                return ['client', $clientId];
            }
        }
        return [null, null];
    }

    private function mentorOwnsClient(int $doctorId, int $clientId): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('appointment')
            ->where('doctor_id', $doctorId)
            ->where('client_id', $clientId)
            ->where('status !=', 'Cancelled')
            ->countAllResults();
        return $count > 0;
    }

    private function fail(string $msg, int $code = 400)
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON(['status' => false, 'msg' => $msg]);
    }
}
