package com.actualite.client;

import java.util.Map;

public class SessionManager {
    private static String token;
    private static Map<String, Object> currentUser;

    public static String getToken() {
        return token;
    }

    public static void setToken(String t) {
        token = t;
    }

    public static Map<String, Object> getCurrentUser() {
        return currentUser;
    }

    public static void setCurrentUser(Map<String, Object> user) {
        currentUser = user;
    }

    public static void clear() {
        token = null;
        currentUser = null;
    }
} 