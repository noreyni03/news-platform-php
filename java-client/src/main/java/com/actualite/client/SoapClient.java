package com.actualite.client;

import com.fasterxml.jackson.databind.ObjectMapper;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import javax.xml.namespace.QName;
import jakarta.xml.ws.Service;
import java.net.URL;
import java.util.List;
import java.util.Map;
import com.actualite.client.ActualitePort;

/**
 * Client SOAP pour communiquer avec le service web PHP
 */
public class SoapClient {
    private static final Logger logger = LoggerFactory.getLogger(SoapClient.class);
    private static final String SOAP_URL = "http://localhost/news-platform-php/backend/api/soap_server.php";
    private static final String NAMESPACE_URI = "http://localhost/news-platform-php/backend/api/soap_server.php";
    
    private final ObjectMapper objectMapper;
    private final Service service;
    
    /**
     * Helper to safely convert a SOAP operation response to a {@link java.util.Map}.<br/>
     * <ul>
     *     <li>If the service already returned a {@code Map}, it is returned as-is.</li>
     *     <li>If the service returned a {@code String}, the string is considered to be a JSON payload.</li>
     *     <li>Empty or null strings are interpreted as an empty map instead of being fed to Jackson – this prevents
     *     the "No content to map due to end-of-input" error that crashes the client at start-up.</li>
     * </ul>
     */
    @SuppressWarnings("unchecked")
    private Map<String, Object> toMap(Object response) throws java.io.IOException {
        if (response == null) {
            return java.util.Collections.emptyMap();
        }
        if (response instanceof Map) {
            return (Map<String, Object>) response;
        }
        if (response instanceof String) {
            String json = ((String) response).trim();
            logger.debug("SOAP raw string response: {}", json.length() > 500 ? json.substring(0,500)+"..." : json);
            if (json.isEmpty()) {
                // Empty payload – return an empty map to avoid a Jackson exception
                return java.util.Collections.emptyMap();
            }
            return objectMapper.readValue(json, Map.class);
        }
        // Fallback – unknown type, wrap it into a singleton map for visibility
        return java.util.Collections.singletonMap("value", response);
    }

    /**
     * Convertit la structure Map générée par l'encodage SOAP (clé "item" contenant une liste clé/valeur)
     * vers une Map Java classique.
     */
    @SuppressWarnings("unchecked")
    private Map<String, Object> normalizeSoapMap(Object anyObj) {
        if (!(anyObj instanceof Map)) {
            return java.util.Collections.emptyMap();
        }
        Map<?, ?> soapMap = (Map<?, ?>) anyObj;
        if (soapMap == null) return java.util.Collections.emptyMap();

        // detect list wrapper keys
        Object listObj = soapMap.get("item") != null ? soapMap.get("item") : soapMap.get("entry");
        if (listObj instanceof java.util.List) {
            java.util.Map<String, Object> flat = new java.util.HashMap<>();
            for (Object entryObj : (java.util.List<?>) listObj) {
                if (entryObj instanceof java.util.Map<?, ?>) {
                    java.util.Map<?, ?> entry = (java.util.Map<?, ?>) entryObj;
                    Object keyObj = entry.get("key");
                    Object valObj = entry.get("value");
                    if (keyObj != null) {
                        // recurse for nested soap maps
                        if (valObj instanceof Map) {
                            flat.put(String.valueOf(keyObj), normalizeSoapMap(valObj));
                        } else {
                            flat.put(String.valueOf(keyObj), valObj);
                        }
                    }
                }
            }
            return flat;
        }
        // also normalize any nested maps already flat
        java.util.Map<String, Object> cleaned = new java.util.HashMap<>();
        for (java.util.Map.Entry<?, ?> e : soapMap.entrySet()) {
            Object v = e.getValue();
            if (v instanceof Map) {
                cleaned.put(String.valueOf(e.getKey()), normalizeSoapMap(v));
            } else {
                cleaned.put(String.valueOf(e.getKey()), v);
            }
        }
        return cleaned;
    }
    
    public SoapClient() throws Exception {
        this.objectMapper = new ObjectMapper();
        
        // Création du service SOAP
        URL url = new URL(SOAP_URL + "?wsdl");
        QName qname = new QName(NAMESPACE_URI, "ActualiteService");
        this.service = Service.create(url, qname);
    }
    
    /**
     * Authentifier un utilisateur
     */
    public Map<String, Object> authenticateUser(String username, String password) {
        try {
            logger.info("Tentative d'authentification pour l'utilisateur: {}", username);
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("authenticateUser", String.class, String.class);
            Object response = method.invoke(result, username, password);
            
            // Conversion de la réponse en Map (robuste)
            Map<String, Object> respMap = normalizeSoapMap(toMap(response));
            if (respMap.containsKey("return") && respMap.get("return") instanceof Map) {
                respMap = normalizeSoapMap(respMap.get("return"));
            }
            System.out.println("DEBUG respMap=" + respMap);
            return respMap;
            
        } catch (Exception e) {
            logger.error("Erreur lors de l'authentification: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de l'authentification", e);
        }
    }
    
    /**
     * Lister tous les utilisateurs
     */
    @SuppressWarnings("unchecked")
    public List<Map<String, Object>> listUsers(String token) {
        try {
            logger.info("Récupération de la liste des utilisateurs");
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("listUsers", String.class);
            Object response = method.invoke(result, token);
            
            Map<String, Object> responseMap = normalizeSoapMap(toMap(response));
            return (List<Map<String, Object>>) responseMap.get("users");
            
        } catch (Exception e) {
            logger.error("Erreur lors de la récupération des utilisateurs: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de la récupération des utilisateurs", e);
        }
    }
    
    /**
     * Obtenir un utilisateur par ID
     */
    public Map<String, Object> getUserById(String token, int userId) {
        try {
            logger.info("Récupération de l'utilisateur avec l'ID: {}", userId);
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("getUserById", String.class, int.class);
            Object response = method.invoke(result, token, userId);
            
            // Conversion de la réponse
            if (response instanceof String) {
                Map<String, Object> responseMap = objectMapper.readValue((String) response, Map.class);
                return (Map<String, Object>) responseMap.get("user");
            }
            
            Map<String, Object> responseMap = (Map<String, Object>) response;
            return (Map<String, Object>) responseMap.get("user");
            
        } catch (Exception e) {
            logger.error("Erreur lors de la récupération de l'utilisateur: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de la récupération de l'utilisateur", e);
        }
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public Map<String, Object> createUser(String token, Map<String, Object> userData) {
        try {
            logger.info("Création d'un nouvel utilisateur: {}", userData.get("username"));
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Conversion des données utilisateur en JSON
            String userDataJson = objectMapper.writeValueAsString(userData);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("createUser", String.class, String.class);
            Object response = method.invoke(result, token, userDataJson);
            
            // Conversion de la réponse
            if (response instanceof String) {
                return objectMapper.readValue((String) response, Map.class);
            }
            
            return (Map<String, Object>) response;
            
        } catch (Exception e) {
            logger.error("Erreur lors de la création de l'utilisateur: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de la création de l'utilisateur", e);
        }
    }
    
    /**
     * Modifier un utilisateur
     */
    public Map<String, Object> updateUser(String token, int userId, Map<String, Object> userData) {
        try {
            logger.info("Modification de l'utilisateur avec l'ID: {}", userId);
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Conversion des données utilisateur en JSON
            String userDataJson = objectMapper.writeValueAsString(userData);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("updateUser", String.class, int.class, String.class);
            Object response = method.invoke(result, token, userId, userDataJson);
            
            // Conversion de la réponse
            if (response instanceof String) {
                return objectMapper.readValue((String) response, Map.class);
            }
            
            return (Map<String, Object>) response;
            
        } catch (Exception e) {
            logger.error("Erreur lors de la modification de l'utilisateur: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de la modification de l'utilisateur", e);
        }
    }
    
    /**
     * Supprimer un utilisateur
     */
    public Map<String, Object> deleteUser(String token, int userId) {
        try {
            logger.info("Suppression de l'utilisateur avec l'ID: {}", userId);
            
            Object result = service.getPort(new QName(NAMESPACE_URI, "ActualitePort"), ActualitePort.class);
            
            // Appel de la méthode SOAP
            java.lang.reflect.Method method = result.getClass().getMethod("deleteUser", String.class, int.class);
            Object response = method.invoke(result, token, userId);
            
            // Conversion de la réponse
            if (response instanceof String) {
                return objectMapper.readValue((String) response, Map.class);
            }
            
            return (Map<String, Object>) response;
            
        } catch (Exception e) {
            logger.error("Erreur lors de la suppression de l'utilisateur: {}", e.getMessage(), e);
            throw new RuntimeException("Erreur lors de la suppression de l'utilisateur", e);
        }
    }
} 