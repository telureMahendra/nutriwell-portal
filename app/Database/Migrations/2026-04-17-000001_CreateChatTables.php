<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Chat module tables.
 *
 * Firestore (shared with the Flutter app) remains the source of truth for
 * real-time message sync. These MySQL tables exist for:
 *   - audit log (who sent what, from which portal, when)
 *   - permission enforcement for restricted documents (Diet Plan, Workout Plan,
 *     Prescription PDFs uploaded by a mentor — must ONLY be reachable from the
 *     chat window of the target client, never a public URL).
 */
class CreateChatTables extends Migration
{
    public function up()
    {
        // -----------------------------------------------------------------
        // chat_messages: audit trail of every message sent via the portal
        // -----------------------------------------------------------------
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'conversation_id'  => ['type' => 'VARCHAR', 'constraint' => 64, 'comment' => 'client_id that owns the Firestore chat/{id} doc'],
            'client_id'        => ['type' => 'INT', 'unsigned' => true],
            'sender_type'      => ['type' => 'ENUM("admin","mentor","client")'],
            'sender_id'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sender_name'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'message'          => ['type' => 'TEXT', 'null' => true],
            'message_type'     => ['type' => 'ENUM("text","image","pdf","file")', 'default' => 'text'],
            'file_url'         => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'file_desc'        => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'category'         => ['type' => 'ENUM("general","diet_plan","workout_plan","prescription")', 'default' => 'general'],
            'restricted'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'comment' => '1 = chat-only (Diet/Workout/Prescription)'],
            'created_at'       => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('conversation_id');
        $this->forge->addKey(['client_id', 'created_at']);
        $this->forge->createTable('chat_messages', true);

        // -----------------------------------------------------------------
        // chat_files: every file uploaded via chat endpoint — used by the
        // chat/file/serve/{id} proxy to check permission before streaming.
        // -----------------------------------------------------------------
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'filename'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'original_name'=> ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'mime_type'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'size_bytes'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'uploader_type'=> ['type' => 'ENUM("admin","mentor","client")'],
            'uploader_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'client_id'    => ['type' => 'INT', 'unsigned' => true, 'comment' => 'chat thread the file belongs to'],
            'category'     => ['type' => 'ENUM("general","diet_plan","workout_plan","prescription")', 'default' => 'general'],
            'restricted'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'   => ['type' => 'DATETIME', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->addKey('filename');
        $this->forge->createTable('chat_files', true);
    }

    public function down()
    {
        $this->forge->dropTable('chat_files', true);
        $this->forge->dropTable('chat_messages', true);
    }
}
