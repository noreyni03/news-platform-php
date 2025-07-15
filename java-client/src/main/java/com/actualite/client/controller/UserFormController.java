package com.actualite.client.controller;

import com.actualite.client.SoapClient;
import com.actualite.client.model.User;
import com.actualite.client.SessionManager;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.ComboBox;
import javafx.scene.control.PasswordField;
import javafx.scene.control.TextField;
import javafx.stage.Stage;
import javafx.scene.Scene;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;

import java.util.HashMap;
import java.util.Map;

public class UserFormController {
    @FXML
    private TextField usernameField;
    @FXML
    private TextField emailField;
    @FXML
    private PasswordField passwordField;
    @FXML
    private ComboBox<String> roleCombo;

    private SoapClient soapClient;
    private User userToEdit; // null si ajout

    public UserFormController() {
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            // Gérer l'erreur d'initialisation si besoin
        }
    }

    @FXML
    public void initialize() {
        // Initialiser la ComboBox avec les rôles disponibles
        ObservableList<String> roles = FXCollections.observableArrayList("visiteur", "editeur", "admin");
        roleCombo.setItems(roles);
    }

    public void setUserToEdit(User user) {
        this.userToEdit = user;
        if (user != null) {
            usernameField.setText(user.getUsername());
            emailField.setText(user.getEmail());
            roleCombo.setValue(user.getRole());
        }
    }

    public void setModeAjout() {
        this.userToEdit = null;
        usernameField.setText("");
        emailField.setText("");
        passwordField.setText("");
        roleCombo.setValue(null);
    }

    @FXML
    private void handleSave() {
        String username = usernameField.getText();
        String email = emailField.getText();
        String password = passwordField.getText();
        String role = roleCombo.getValue();
        if (username.isEmpty() || email.isEmpty() || role == null) {
            showAlert("Champs manquants", "Veuillez remplir tous les champs obligatoires.", Alert.AlertType.WARNING);
            return;
        }
        Map<String, Object> userData = new HashMap<>();
        userData.put("username", username);
        userData.put("email", email);
        if (!password.isEmpty()) userData.put("password", password);
        userData.put("role", role);
        try {
            String token = SessionManager.getToken();
            Map<String, Object> response;
            if (userToEdit == null) {
                response = soapClient.createUser(token, userData);
            } else {
                response = soapClient.updateUser(token, userToEdit.getId(), userData);
            }
            boolean success = false;
            Object successObj = response.get("success");
            if (successObj instanceof Boolean) {
                success = (Boolean) successObj;
            } else if (successObj instanceof String) {
                success = Boolean.parseBoolean((String) successObj);
            }
            if (success) {
                showAlert("Succès", String.valueOf(response.get("message")), Alert.AlertType.INFORMATION);
                goBack();
            } else {
                showAlert("Erreur", String.valueOf(response.get("message")), Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleCancel() {
        goBack();
    }

    private void goBack() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/UserListView.fxml"));
            Parent root = loader.load();
            Stage stage = (Stage) usernameField.getScene().getWindow();
            stage.setScene(new Scene(root));
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage(), Alert.AlertType.ERROR);
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