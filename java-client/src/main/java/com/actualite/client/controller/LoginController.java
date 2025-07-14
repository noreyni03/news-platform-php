package com.actualite.client.controller;

import com.actualite.client.SoapClient;
import com.actualite.client.SessionManager;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import javafx.scene.Scene;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;

import java.io.StringWriter;
import java.io.PrintWriter;
import java.util.Map;

public class LoginController {
    @FXML
    private TextField usernameField;
    @FXML
    private PasswordField passwordField;

    private SoapClient soapClient;

    public LoginController() {
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            // Gérer l'erreur d'initialisation si besoin
        }
    }

    @FXML
    private void handleLogin(ActionEvent event) {
        String username = usernameField.getText();
        String password = passwordField.getText();
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
                Map<String, Object> user = (Map<String, Object>) response.get("user");
                String role = (String) user.get("role");
                if (!"admin".equals(role)) {
                    showAlert("Accès refusé", "Droits administrateur requis.", Alert.AlertType.ERROR);
                    return;
                }
                // Stocker le token et l'utilisateur courant
                SessionManager.setToken((String) response.get("token"));
                SessionManager.setCurrentUser(user);
                // Navigation vers le menu principal
                FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/MainView.fxml"));
                Parent root = loader.load();
                Stage stage = (Stage) usernameField.getScene().getWindow();
                stage.setScene(new Scene(root));
            } else {
                String message = response.get("message") != null ? String.valueOf(response.get("message")) : "Authentification échouée";
                showAlert("Erreur", message, Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            // Afficher le stacktrace complet dans la console
            System.err.println("=== ERREUR COMPLÈTE ===");
            e.printStackTrace();
            System.err.println("=== FIN ERREUR ===");
            
            // Afficher une alerte avec les détails de l'erreur
            StringWriter sw = new StringWriter();
            PrintWriter pw = new PrintWriter(sw);
            e.printStackTrace(pw);
            String stackTrace = sw.toString();
            
            showAlert("Erreur détaillée", 
                     "Type d'exception: " + e.getClass().getSimpleName() + 
                     "\nMessage: " + e.getMessage() + 
                     "\n\nStacktrace complet:\n" + stackTrace, 
                     Alert.AlertType.ERROR);
        }
    }

    private void showAlert(String title, String content, Alert.AlertType type) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setHeaderText(null);
        alert.setContentText(content);
        alert.showAndWait();
    }
} 