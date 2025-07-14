package com.actualite.client.controller;

import com.actualite.client.SceneManager;
import com.actualite.client.Session;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;

public class DashboardController {

    @FXML
    private void onUsers(ActionEvent event) {
        SceneManager.switchTo("/com/actualite/client/view/UsersListView.fxml", "Utilisateurs");
    }

    @FXML
    private void onLogout(ActionEvent event) {
        Session.clear();
        SceneManager.switchTo("/com/actualite/client/view/LoginView.fxml", "Connexion");
    }
}
