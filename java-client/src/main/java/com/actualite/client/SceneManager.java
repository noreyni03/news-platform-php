package com.actualite.client;

import javafx.fxml.FXMLLoader;
import javafx.scene.Scene;
import javafx.stage.Stage;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.IOException;
import java.net.URL;

/**
 * Gestionnaire centralisé de navigation entre les vues JavaFX.
 */
public class SceneManager {
    private static final Logger logger = LoggerFactory.getLogger(SceneManager.class);
    private static Stage primaryStage;

    public static void setStage(Stage stage) {
        primaryStage = stage;
    }

    public static void switchTo(String fxmlPath, String title) {
        try {
            URL url = SceneManager.class.getResource(fxmlPath);
            if (url == null) {
                throw new IllegalArgumentException("FXML introuvable: " + fxmlPath);
            }
            FXMLLoader loader = new FXMLLoader(url);
            Scene scene = new Scene(loader.load());
            primaryStage.setScene(scene);
            primaryStage.setTitle(title);
            primaryStage.centerOnScreen();
        } catch (IOException e) {
            logger.error("Erreur de chargement de la vue {}", fxmlPath, e);
        }
    }
}
