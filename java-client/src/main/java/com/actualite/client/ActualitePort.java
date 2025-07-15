package com.actualite.client;

import jakarta.jws.WebMethod;
import jakarta.jws.WebService;

/**
 * Interface Service Endpoint (SEI) correspondant au service SOAP PHP.
 * Les méthodes retournent des chaînes JSON que l'application parse ensuite.
 */
@WebService(targetNamespace = "http://localhost/news-platform-php/backend/api/soap_server.php", name = "ActualitePort")
public interface ActualitePort {

    @WebMethod
    String authenticateUser(String username, String password);

    @WebMethod
    String createUser(String token, String userDataJson);

    @WebMethod
    String updateUser(String token, int userId, String userDataJson);

    @WebMethod
    String deleteUser(String token, int userId);

    @WebMethod
    String listUsers(String token);

    @WebMethod
    String getUserById(String token, int userId);
}
