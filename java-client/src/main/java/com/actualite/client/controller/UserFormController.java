package com.actualite.client.controller;

import com.actualite.client.SceneManager;
import com.actualite.client.Session;
import com.actualite.client.SoapClient;
import javafx.collections.FXCollections;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.ChoiceBox;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.HashMap;
import java.util.Map;

public class UserFormController {
    private static final Logger logger = LoggerFactory.getLogger(UserFormController.class);

    @FXML
    private TextField usernameField;
    @FXML
    private TextField emailField;
    @FXML
    private PasswordField passwordField;
    @FXML
    private ChoiceBox<String> roleChoice;

    private SoapClient soapClient;

    @FXML
    public void initialize() {
        roleChoice.setItems(FXCollections.observableArrayList("visiteur", "editeur", "admin"));
        roleChoice.setValue("visiteur");
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            logger.error("Erreur init SoapClient", e);
            showError(e.getMessage());
        }
    }

    @FXML
    private void onSave(ActionEvent event) {
        String username = usernameField.getText();
        String email = emailField.getText();
        String password = passwordField.getText();
        String role = roleChoice.getValue();

        if (username.isEmpty() || email.isEmpty() || password.isEmpty()) {
            showError("Tous les champs sont obligatoires.");
            return;
        }

        Map<String, Object> data = new HashMap<>();
        data.put("username", username);
        data.put("email", email);
        data.put("password", password);
        data.put("role", role);

        try {
            Map<String, Object> resp = soapClient.createUser(Session.getToken(), data);
            if (Boolean.parseBoolean(String.valueOf(resp.get("success")))) {
                Alert success = new Alert(Alert.AlertType.INFORMATION, "Utilisateur créé avec succès !");
                success.showAndWait();
                SceneManager.switchTo("/com/actualite/client/view/UsersListView.fxml", "Utilisateurs");
            } else {
                showError(String.valueOf(resp.get("message")));
            }
        } catch (Exception e) {
            logger.error("Erreur création utilisateur", e);
            if (e.getMessage() != null && (e.getMessage().contains("Connection refused") || e.getMessage().contains("connect"))) {
                showError("Erreur réseau : impossible de contacter le serveur. Veuillez vérifier votre connexion.");
            } else {
                showError(e.getMessage());
            }
        }
    }

    @FXML
    private void onCancel(ActionEvent event) {
        SceneManager.switchTo("/com/actualite/client/view/UsersListView.fxml", "Utilisateurs");
    }

    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR, msg);
        alert.showAndWait();
    }
}
