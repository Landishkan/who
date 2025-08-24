// firebase.js
const firebaseConfig = {
    apiKey: "AIzaSyAjDJPWVXFmBlWYRUf-IACsQrut_lEEFfc",
    authDomain: "cyber-questor.firebaseapp.com",
    projectId: "cyber-questor",
    storageBucket: "cyber-questor.firebasestorage.app",
    messagingSenderId: "347078202285",
    appId: "1:347078202285:web:7ac8581765d5fdc76a4724",
    measurementId: "G-GWS13K2FW1"
};

// Инициализация Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
const db = firebase.firestore();