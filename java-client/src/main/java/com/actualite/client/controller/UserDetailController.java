package com.actualite.client.controller;

import com.actualite.client.SceneManager;
import com.actualite.client.Session;
import com.actualite.client.SoapClient;
import com.actualite.client.User;
import javafx.collections.FXCollections;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.ChoiceBox;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.scene.control.Label;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.time.format.DateTimeFormatter;
import java.util.HashMap;
import java.util.Map;

public class UserDetailController {
    private static final Logger logger = LoggerFactory.getLogger(UserDetailController.class);

    @FXML
    private TextField usernameField;
    @FXML
    private TextField emailField;
    @FXML
    private PasswordField passwordField;
    @FXML
    private ChoiceBox<String> roleChoice;
    @FXML
    private Label createdAtLabel;

    private SoapClient soapClient;
    private User user;

    @FXML
    public void initialize() {
        roleChoice.setItems(FXCollections.observableArrayList("visiteur", "editeur", "admin"));
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            logger.error("Erreur init SoapClient", e);
            showError(e.getMessage());
        }
        user = Session.getSelectedUser();
        if (user != null) {
            usernameField.setText(user.getUsername());
            emailField.setText(user.getEmail());
            roleChoice.setValue(user.getRole());
            if (user.getCreatedAt() != null) {
                createdAtLabel.setText(user.getCreatedAt().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm")));
            } else {
                createdAtLabel.setText("");
            }
        }
    }

    @FXML
    private void onSave(ActionEvent event) {
        if (user == null) return;
        String username = usernameField.getText();
        String email = emailField.getText();
        String password = passwordField.getText();
        String role = roleChoice.getValue();

        if (username.isEmpty() || email.isEmpty()) {
            showError("Nom d'utilisateur et email obligatoires.");
            return;
        }

        Map<String, Object> data = new HashMap<>();
        data.put("username", username);
        data.put("email", email);
        data.put("role", role);
        if (!password.isEmpty()) {
            data.put("password", password);
        }

        try {
            Map<String, Object> resp = soapClient.updateUser(Session.getToken(), user.getId(), data);
            if (Boolean.parseBoolean(String.valueOf(resp.get("success")))) {
                Alert success = new Alert(Alert.AlertType.INFORMATION, "Utilisateur modifié avec succès !");
                success.showAndWait();
                SceneManager.switchTo("/com/actualite/client/view/UsersListView.fxml", "Utilisateurs");
            } else {
                showError(String.valueOf(resp.get("message")));
            }
        } catch (Exception e) {
            logger.error("Erreur modification utilisateur", e);
            if (e.getMessage() != null && (e.getMessage().contains("Connection refused") || e.getMessage().contains("connect"))) {
                showError("Erreur réseau : impossible de contacter le serveur. Veuillez vérifier votre connexion.");
            } else {
                showError(e.getMessage());
            }
        }
    }

    @FXML
    private void onDelete(ActionEvent event) {
        if (user == null) return;
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION, "Supprimer l'utilisateur " + user.getUsername() + " ?");
        confirm.showAndWait().ifPresent(btn -> {
            if (btn == javafx.scene.control.ButtonType.OK || btn == javafx.scene.control.ButtonType.YES) {
                try {
                    Map<String, Object> resp = soapClient.deleteUser(Session.getToken(), user.getId());
                    if (Boolean.parseBoolean(String.valueOf(resp.get("success")))) {
                        Alert success = new Alert(Alert.AlertType.INFORMATION, "Utilisateur supprimé avec succès !");
                        success.showAndWait();
                        SceneManager.switchTo("/com/actualite/client/view/UsersListView.fxml", "Utilisateurs");
                    } else {
                        showError(String.valueOf(resp.get("message")));
                    }
                } catch (Exception e) {
                    logger.error("Erreur suppression utilisateur", e);
                    if (e.getMessage() != null && (e.getMessage().contains("Connection refused") || e.getMessage().contains("connect"))) {
                        showError("Erreur réseau : impossible de contacter le serveur. Veuillez vérifier votre connexion.");
                    } else {
                        showError(e.getMessage());
                    }
                }
            }
        });
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