package com.actualite.client;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.*;

/**
 * Application principale pour la gestion des utilisateurs
 */
public class UserManagementApp {
    private static final Logger logger = LoggerFactory.getLogger(UserManagementApp.class);
    private static final Scanner scanner = new Scanner(System.in);
    
    private SoapClient soapClient;
    private String currentToken;
    private Map<String, Object> currentUser;
    
    public UserManagementApp() {
        try {
            this.soapClient = new SoapClient();
        } catch (Exception e) {
            logger.error("Erreur lors de l'initialisation du client SOAP: {}", e.getMessage());
            System.err.println("Erreur: Impossible de se connecter au service web SOAP");
            System.exit(1);
        }
    }
    
    public void run() {
        System.out.println("=== APPLICATION DE GESTION DES UTILISATEURS ===");
        System.out.println("Connexion au service web SOAP...");
        
        if (authenticate()) {
            showMainMenu();
        } else {
            System.out.println("Échec de l'authentification. L'application se ferme.");
        }
    }
    
    private boolean authenticate() {
        System.out.println("\n--- AUTHENTIFICATION ---");
        System.out.print("Nom d'utilisateur: ");
        String username = scanner.nextLine();
        
        System.out.print("Mot de passe: ");
        String password = scanner.nextLine();
        
        try {
            Map<String, Object> response = soapClient.authenticateUser(username, password);
            
            Object successObj = response.get("success");
            boolean success = false;
            if (successObj instanceof Boolean) {
                success = (Boolean) successObj;
            } else if (successObj instanceof String) {
                success = Boolean.parseBoolean((String) successObj);
            }
            
            if (success) {
                currentUser = (Map<String, Object>) response.get("user");
                currentToken = (String) response.get("token");
                
                System.out.println("✓ Authentification réussie!");
                System.out.println("Bienvenue, " + currentUser.get("username") + " (" + currentUser.get("role") + ")");
                
                // Vérifier les droits d'administration
                if (!"admin".equals(currentUser.get("role"))) {
                    System.out.println("❌ Accès refusé: droits administrateur requis");
                    return false;
                }
                
                return true;
            } else {
                String message = response.get("message") != null ? String.valueOf(response.get("message")) : "Authentification échouée";
                System.out.println("❌ " + message);
                return false;
            }
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de l'authentification: " + e.getMessage());
            return false;
        }
    }
    
    private void showMainMenu() {
        while (true) {
            System.out.println("\n=== MENU PRINCIPAL ===");
            System.out.println("1. Lister tous les utilisateurs");
            System.out.println("2. Voir un utilisateur");
            System.out.println("3. Créer un nouvel utilisateur");
            System.out.println("4. Modifier un utilisateur");
            System.out.println("5. Supprimer un utilisateur");
            System.out.println("0. Quitter");
            
            System.out.print("\nVotre choix: ");
            String choice = scanner.nextLine();
            
            switch (choice) {
                case "1":
                    listUsers();
                    break;
                case "2":
                    viewUser();
                    break;
                case "3":
                    createUser();
                    break;
                case "4":
                    updateUser();
                    break;
                case "5":
                    deleteUser();
                    break;
                case "0":
                    System.out.println("Au revoir!");
                    return;
                default:
                    System.out.println("Choix invalide. Veuillez réessayer.");
            }
        }
    }
    
    private void listUsers() {
        try {
            System.out.println("\n--- LISTE DES UTILISATEURS ---");
            List<Map<String, Object>> users = soapClient.listUsers(currentToken);
            
            if (users.isEmpty()) {
                System.out.println("Aucun utilisateur trouvé.");
                return;
            }
            
            System.out.printf("%-5s %-20s %-30s %-15s %-20s%n", "ID", "Nom d'utilisateur", "Email", "Rôle", "Date de création");
            System.out.println("-".repeat(90));
            
            for (Map<String, Object> user : users) {
                System.out.printf("%-5s %-20s %-30s %-15s %-20s%n",
                        user.get("id"),
                        user.get("username"),
                        user.get("email"),
                        user.get("role"),
                        user.get("created_at"));
            }
            
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de la récupération des utilisateurs: " + e.getMessage());
        }
    }
    
    private void viewUser() {
        try {
            System.out.print("\nID de l'utilisateur à consulter: ");
            int userId = Integer.parseInt(scanner.nextLine());
            
            Map<String, Object> user = soapClient.getUserById(currentToken, userId);
            
            if (user != null) {
                System.out.println("\n--- DÉTAILS DE L'UTILISATEUR ---");
                System.out.println("ID: " + user.get("id"));
                System.out.println("Nom d'utilisateur: " + user.get("username"));
                System.out.println("Email: " + user.get("email"));
                System.out.println("Rôle: " + user.get("role"));
                System.out.println("Date de création: " + user.get("created_at"));
            } else {
                System.out.println("❌ Utilisateur non trouvé.");
            }
            
        } catch (NumberFormatException e) {
            System.out.println("❌ ID invalide.");
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de la récupération de l'utilisateur: " + e.getMessage());
        }
    }
    
    private void createUser() {
        try {
            System.out.println("\n--- CRÉATION D'UN NOUVEL UTILISATEUR ---");
            
            System.out.print("Nom d'utilisateur: ");
            String username = scanner.nextLine();
            
            System.out.print("Email: ");
            String email = scanner.nextLine();
            
            System.out.print("Mot de passe: ");
            String password = scanner.nextLine();
            
            System.out.print("Rôle (visiteur/editeur/admin): ");
            String role = scanner.nextLine();
            
            if (!Arrays.asList("visiteur", "editeur", "admin").contains(role)) {
                System.out.println("❌ Rôle invalide. Utilisation du rôle 'visiteur' par défaut.");
                role = "visiteur";
            }
            
            Map<String, Object> userData = new HashMap<>();
            userData.put("username", username);
            userData.put("email", email);
            userData.put("password", password);
            userData.put("role", role);
            
            Map<String, Object> response = soapClient.createUser(currentToken, userData);
            
            if ((Boolean) response.get("success")) {
                System.out.println("✓ " + response.get("message"));
            } else {
                System.out.println("❌ " + response.get("message"));
            }
            
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de la création de l'utilisateur: " + e.getMessage());
        }
    }
    
    private void updateUser() {
        try {
            System.out.print("\nID de l'utilisateur à modifier: ");
            int userId = Integer.parseInt(scanner.nextLine());
            
            System.out.println("Laissez vide les champs que vous ne voulez pas modifier.");
            
            System.out.print("Nouveau nom d'utilisateur: ");
            String username = scanner.nextLine();
            
            System.out.print("Nouvel email: ");
            String email = scanner.nextLine();
            
            System.out.print("Nouveau mot de passe: ");
            String password = scanner.nextLine();
            
            System.out.print("Nouveau rôle (visiteur/editeur/admin): ");
            String role = scanner.nextLine();
            
            Map<String, Object> userData = new HashMap<>();
            if (!username.isEmpty()) userData.put("username", username);
            if (!email.isEmpty()) userData.put("email", email);
            if (!password.isEmpty()) userData.put("password", password);
            if (!role.isEmpty()) {
                if (Arrays.asList("visiteur", "editeur", "admin").contains(role)) {
                    userData.put("role", role);
                } else {
                    System.out.println("❌ Rôle invalide. Le rôle ne sera pas modifié.");
                }
            }
            
            if (userData.isEmpty()) {
                System.out.println("❌ Aucune modification spécifiée.");
                return;
            }
            
            Map<String, Object> response = soapClient.updateUser(currentToken, userId, userData);
            
            if ((Boolean) response.get("success")) {
                System.out.println("✓ " + response.get("message"));
            } else {
                System.out.println("❌ " + response.get("message"));
            }
            
        } catch (NumberFormatException e) {
            System.out.println("❌ ID invalide.");
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de la modification de l'utilisateur: " + e.getMessage());
        }
    }
    
    private void deleteUser() {
        try {
            System.out.print("\nID de l'utilisateur à supprimer: ");
            int userId = Integer.parseInt(scanner.nextLine());
            
            System.out.print("Êtes-vous sûr de vouloir supprimer cet utilisateur? (oui/non): ");
            String confirmation = scanner.nextLine();
            
            if ("oui".equalsIgnoreCase(confirmation)) {
                Map<String, Object> response = soapClient.deleteUser(currentToken, userId);
                
                if ((Boolean) response.get("success")) {
                    System.out.println("✓ " + response.get("message"));
                } else {
                    System.out.println("❌ " + response.get("message"));
                }
            } else {
                System.out.println("Suppression annulée.");
            }
            
        } catch (NumberFormatException e) {
            System.out.println("❌ ID invalide.");
        } catch (Exception e) {
            System.out.println("❌ Erreur lors de la suppression de l'utilisateur: " + e.getMessage());
        }
    }
    
    public static void main(String[] args) {
        UserManagementApp app = new UserManagementApp();
        app.run();
    }
} 