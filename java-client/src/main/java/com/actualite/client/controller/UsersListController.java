package com.actualite.client.controller;

import com.actualite.client.SceneManager;
import com.actualite.client.Session;
import com.actualite.client.SoapClient;
import com.actualite.client.User;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.scene.control.Alert;
import javafx.scene.control.TableColumn;
import javafx.scene.control.ButtonType;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

public class UsersListController {
    private static final Logger logger = LoggerFactory.getLogger(UsersListController.class);

    @FXML
    private TableView<User> table;
    @FXML
    private TableColumn<User, Integer> colId;
    @FXML
    private TableColumn<User, String> colUsername;
    @FXML
    private TableColumn<User, String> colEmail;
    @FXML
    private TableColumn<User, String> colRole;

    private SoapClient soapClient;

    @FXML
    public void initialize() {
        try {
            soapClient = new SoapClient();
        } catch (Exception e) {
            logger.error("Erreur init SoapClient", e);
            showError(e.getMessage());
        }

        colId.setCellValueFactory(new PropertyValueFactory<>("id"));
        colUsername.setCellValueFactory(new PropertyValueFactory<>("username"));
        colEmail.setCellValueFactory(new PropertyValueFactory<>("email"));
        colRole.setCellValueFactory(new PropertyValueFactory<>("role"));

        // Row factory for double click and context menu
        table.setRowFactory(tv -> {
            javafx.scene.control.TableRow<User> row = new javafx.scene.control.TableRow<>();
            row.setOnMouseClicked(evt -> {
                if (evt.getClickCount() == 2 && !row.isEmpty()) {
                    openUserDetail(row.getItem());
                }
            });
            javafx.scene.control.ContextMenu menu = new javafx.scene.control.ContextMenu();
            javafx.scene.control.MenuItem view = new javafx.scene.control.MenuItem("Voir / Modifier");
            view.setOnAction(e -> openUserDetail(row.getItem()));
            javafx.scene.control.MenuItem deleteMi = new javafx.scene.control.MenuItem("Supprimer");
            deleteMi.setOnAction(e -> deleteUser(row.getItem()));
            menu.getItems().addAll(view, deleteMi);
            row.contextMenuProperty().bind(javafx.beans.binding.Bindings.when(row.emptyProperty()).then((javafx.scene.control.ContextMenu) null).otherwise(menu));
            return row;
        });

        loadUsers();
    }

    private void loadUsers() {
        try {
            List<Map<String, Object>> usersData = soapClient.listUsers(Session.getToken());
            if (usersData == null) {
                showError("Erreur lors de la récupération des utilisateurs");
                return;
            }
            if (usersData.isEmpty()) {
                showError("Aucun utilisateur trouvé");
            }

            List<User> users = usersData.stream().map(this::mapToUser).collect(Collectors.toList());
            ObservableList<User> data = FXCollections.observableArrayList(users);
            table.setItems(data);
        } catch (Exception e) {
            logger.error("Erreur chargement utilisateurs", e);
            showError(e.getMessage());
        }
    }

    private User mapToUser(Map<String, Object> map) {
        User u = new User();
        u.setId(Integer.parseInt(String.valueOf(map.get("id"))));
        u.setUsername(String.valueOf(map.get("username")));
        u.setEmail(String.valueOf(map.get("email")));
        u.setRole(String.valueOf(map.get("role")));
        return u;
    }

    @FXML
    private void onCreate(ActionEvent event) {
        SceneManager.switchTo("/com/actualite/client/view/UserFormView.fxml", "Créer un utilisateur");
    }

    @FXML
    private void onRefresh(ActionEvent event) {
        loadUsers();
    }

    @FXML
    private void onBack(ActionEvent event) {
        SceneManager.switchTo("/com/actualite/client/view/DashboardView.fxml", "Tableau de bord");
    }

    private void openUserDetail(User user) {
        Session.setSelectedUser(user);
        SceneManager.switchTo("/com/actualite/client/view/UserDetailView.fxml", "Utilisateur " + user.getUsername());
    }

    private void deleteUser(User user) {
        Alert confirm = new Alert(Alert.AlertType.CONFIRMATION, "Supprimer l'utilisateur " + user.getUsername() + " ?", ButtonType.YES, ButtonType.NO);
        confirm.showAndWait().ifPresent(btn -> {
            if (btn == ButtonType.YES) {
                try {
                    Map<String, Object> resp = soapClient.deleteUser(Session.getToken(), user.getId());
                    if (Boolean.parseBoolean(String.valueOf(resp.get("success")))) {
                        Alert success = new Alert(Alert.AlertType.INFORMATION, "Utilisateur supprimé avec succès !");
                        success.showAndWait();
                        loadUsers();
                    } else {
                        showError(String.valueOf(resp.get("message")));
                    }
                } catch (Exception e) {
                    logger.error("Erreur suppression", e);
                    if (e.getMessage() != null && (e.getMessage().contains("Connection refused") || e.getMessage().contains("connect"))) {
                        showError("Erreur réseau : impossible de contacter le serveur. Veuillez vérifier votre connexion.");
                    } else {
                        showError(e.getMessage());
                    }
                }
            }
        });
    }

    private void showError(String msg) {
        Alert alert = new Alert(Alert.AlertType.ERROR, msg);
        alert.showAndWait();
    }
}
