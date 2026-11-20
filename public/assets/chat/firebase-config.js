/**
 * Firebase Web SDK config for the portal chat widget.
 * Project: healthyai-f9fdb
 *
 * This must point to the SAME Firebase project the Flutter app uses — the
 * portal and the mobile app read/write the same `chat/{clientId}` documents.
 */
window.H4U_FIREBASE_CONFIG = {
  apiKey:            "AIzaSyBVO2we5g3cvA_atya46ozlNQ7YjLEvE2k",
  authDomain:        "healthyai-f9fdb.firebaseapp.com",
  projectId:         "healthyai-f9fdb",
  storageBucket:     "healthyai-f9fdb.firebasestorage.app",
  messagingSenderId: "937981425998",
  appId:             "1:937981425998:web:0fa976d2619b6602e4c61f",
  measurementId:     "G-VBGM8EM5WK"
};

/** Firestore collection — must match the Flutter app. Do NOT change. */
window.H4U_CHAT_COLLECTION = "chat";
