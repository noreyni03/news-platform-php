package com.actualite.client;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.stage.Stage;

/**
 * Point d'entrée JavaFX. Charge la vue de connexion.
 */
public class App extends Application {

    @Override
    public void start(Stage primaryStage) throws Exception {
        FXMLLoader loader = new FXMLLoader(getClass().getResource("/com/actualite/client/view/LoginView.fxml"));
        Scene scene = new Scene(loader.load());
        SceneManager.setStage(primaryStage);
        primaryStage.setTitle("Gestion des utilisateurs");
        primaryStage.setScene(scene);
        primaryStage.setResizable(false);
        primaryStage.show();
    }

    public static void main(String[] args) {
        launch(args);
    }
}
