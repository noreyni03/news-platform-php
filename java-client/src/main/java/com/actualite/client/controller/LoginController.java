package com.actualite.client.controller;

import com.actualite.client.SoapClient;
import com.actualite.client.SceneManager;
import com.actualite.client.Session;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.Map;

public class LoginController {
    private static final Logger logger = LoggerFactory.getLogger(LoginController.class);

    @FXML
    private TextField usernameField;
    @FXML
    private PasswordField passwordField;

    private SoapClient soapClient;

    @FXML
    public void initialize() {
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            logger.error("Impossible d'initialiser le client SOAP", e);
            showError("Erreur d'initialisation du client SOAP: " + e.getMessage());
        }
    }

    @FXML
    private void onLogin(ActionEvent event) {
        String username = usernameField.getText();
        String password = passwordField.getText();

        try {
            Map<String, Object> resp = soapClient.authenticateUser(username, password);
            boolean success = Boolean.parseBoolean(String.valueOf(resp.get("success")));
            if (success) {
                // Stocker session
                Session.setToken((String) resp.get("token"));
                Session.setCurrentUser((Map<String, Object>) resp.get("user"));
                Alert alert = new Alert(Alert.AlertType.INFORMATION, "Connexion réussie !");
                alert.showAndWait();

                // Ne pas fermer la fenêtre principale ici !
                // Stage stage = (Stage) usernameField.getScene().getWindow();
                // stage.close();

                SceneManager.switchTo("/com/actualite/client/view/DashboardView.fxml", "Tableau de bord");
            } else {
                showError(String.valueOf(resp.get("message")));
            }
        } catch (Exception e) {
            logger.error("Erreur lors de la connexion", e);
            if (e.getMessage() != null && (e.getMessage().contains("Connection refused") || e.getMessage().contains("connect"))) {
                showError("Erreur réseau : impossible de contacter le serveur. Veuillez vérifier votre connexion.");
            } else {
                showError(e.getMessage());
            }
        }
    }

    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR, msg);
        alert.showAndWait();
    }
}
