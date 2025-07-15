package com.actualite.client.controller;

import com.actualite.client.SoapClient;
import com.actualite.client.SessionManager;
import com.actualite.client.model.User;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.stage.Stage;

import java.util.List;
import java.util.Map;

public class UserListController {
    @FXML
    private TableView<User> userTable;
    @FXML
    private TableColumn<User, Integer> idColumn;
    @FXML
    private TableColumn<User, String> usernameColumn;
    @FXML
    private TableColumn<User, String> emailColumn;
    @FXML
    private TableColumn<User, String> roleColumn;
    @FXML
    private TableColumn<User, String> createdAtColumn;

    private SoapClient soapClient;

    public UserListController() {
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            // Gérer l'erreur d'initialisation si besoin
        }
    }

    @FXML
    public void initialize() {
        idColumn.setCellValueFactory(new PropertyValueFactory<>("id"));
        usernameColumn.setCellValueFactory(new PropertyValueFactory<>("username"));
        emailColumn.setCellValueFactory(new PropertyValueFactory<>("email"));
        roleColumn.setCellValueFactory(new PropertyValueFactory<>("role"));
        createdAtColumn.setCellValueFactory(new PropertyValueFactory<>("createdAt"));
        loadUsers();
    }

    private void loadUsers() {
        try {
            String token = SessionManager.getToken();
            List<Map<String, Object>> users = soapClient.listUsers(token);
            ObservableList<User> userList = FXCollections.observableArrayList();
            for (Map<String, Object> u : users) {
                userList.add(new User(
                        (int) u.get("id"),
                        (String) u.get("username"),
                        (String) u.get("email"),
                        (String) u.get("role"),
                        (String) u.get("created_at")
                ));
            }
            userTable.setItems(userList);
        } catch (Exception e) {
            showAlert("Erreur", "Impossible de charger les utilisateurs: " + e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleAddUser() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/UserFormView.fxml"));
            Parent root = loader.load();
            UserFormController formController = loader.getController();
            formController.setModeAjout();
            Stage stage = (Stage) userTable.getScene().getWindow();
            stage.setScene(new Scene(root));
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleEditUser() {
        User selected = userTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Aucun utilisateur sélectionné", "Veuillez sélectionner un utilisateur à modifier.", Alert.AlertType.WARNING);
            return;
        }
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/UserFormView.fxml"));
            Parent root = loader.load();
            UserFormController formController = loader.getController();
            formController.setUserToEdit(selected);
            Stage stage = (Stage) userTable.getScene().getWindow();
            stage.setScene(new Scene(root));
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleDeleteUser() {
        User selected = userTable.getSelectionModel().getSelectedItem();
        if (selected == null) {
            showAlert("Aucun utilisateur sélectionné", "Veuillez sélectionner un utilisateur à supprimer.", Alert.AlertType.WARNING);
            return;
        }
        try {
            String token = SessionManager.getToken();
            Map<String, Object> response = soapClient.deleteUser(token, selected.getId());
            boolean success = false;
            Object successObj = response.get("success");
            if (successObj instanceof Boolean) {
                success = (Boolean) successObj;
            } else if (successObj instanceof String) {
                success = Boolean.parseBoolean((String) successObj);
            }
            if (success) {
                showAlert("Succès", String.valueOf(response.get("message")), Alert.AlertType.INFORMATION);
                loadUsers();
            } else {
                showAlert("Erreur", String.valueOf(response.get("message")), Alert.AlertType.ERROR);
            }
        } catch (Exception e) {
            showAlert("Erreur", e.getMessage(), Alert.AlertType.ERROR);
        }
    }

    @FXML
    private void handleBack() {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/MainView.fxml"));
            Parent root = loader.load();
            Stage stage = (Stage) userTable.getScene().getWindow();
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