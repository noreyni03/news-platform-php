package com.actualite.client;

import java.util.Map;

/**
 * Stocke l'utilisateur courant et le token d'authentification
 */
public class Session {
    private static String token;
    private static Map<String, Object> currentUser;
    private static com.actualite.client.User selectedUser;

    public static String getToken() {
        return token;
    }

    public static void setToken(String token) {
        Session.token = token;
    }

    public static Map<String, Object> getCurrentUser() {
        return currentUser;
    }

    public static void setCurrentUser(Map<String, Object> currentUser) {
        Session.currentUser = currentUser;
    }

    public static com.actualite.client.User getSelectedUser() {
        return selectedUser;
    }

    public static void setSelectedUser(com.actualite.client.User user) {
        selectedUser = user;
    }

    public static void clear() {
        token = null;
        currentUser = null;
        selectedUser = null;
    }
}
