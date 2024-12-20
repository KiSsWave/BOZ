import 'dart:convert';
import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:flutter_dotenv/flutter_dotenv.dart';

class RemoteService {
  final storage = const FlutterSecureStorage();
  final baseUrl = dotenv.env['BASE_URL']; // Chargement de l'URL de base

  // Inscription d'un utilisateur
  Future<http.Response> registerUser(
      String email, String username, String password) async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$baseUrl/register'); // Utilisation de baseUrl

      var response = await client.post(uri,
          body: {"email": email, "login": username, "password": password});

      if (response.statusCode != 200) {
        return http.Response(
            'Registration failed: ${response.body}', response.statusCode);
      }

      return response;
    } catch (e) {
      print("Error during registration: $e");
      return http.Response('Error during registration: $e', 500);
    }
  }

  // Connexion d'un utilisateur
  Future<http.Response> loginUser(String username, String password) async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$baseUrl/signin'); // Utilisation de baseUrl

      var response = await client
          .post(uri, body: {"email": username, "password": password});

      if (response.statusCode != 200) {
        return http.Response(
            'Login failed: ${response.body}', response.statusCode);
      }

      var token = jsonDecode(response.body)['token'];
      var role = jsonDecode(response.body)['role']; // Récupérer le rôle

      if (token == null) {
        return http.Response('Missing authorization token', 401);
      }

      // Enregistrer le token et le rôle dans le stockage sécurisé
      await storage.write(key: 'jwt', value: token);
      await storage.write(key: 'role', value: role); // Stockage du rôle

      return response;
    } catch (e) {
      print("Error during login: $e");
      return http.Response('Error during login: $e', 500);
    }
  }

  // Déconnexion de l'utilisateur
  disconnectUser() async {
    await storage.delete(key: 'jwt');
    await storage.delete(key: 'role'); // Supprimer également le rôle
  }
  
  // Vérifier si l'utilisateur est connecté
  Future<bool> isConnected() async {
    return await storage.read(key: 'jwt') != null;
  }

  // Récupérer le solde de l'utilisateur
  Future<http.Response> fetchBalance() async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$baseUrl/balance'); // Utilisation de baseUrl

      var token = await storage.read(key: 'jwt');

      if (token == null) {
        return http.Response('Missing authorization token', 401);
      }

      var response = await client.get(uri, headers: {
        HttpHeaders.authorizationHeader: 'Bearer $token',
      });

      if (response.statusCode != 200) {
        return http.Response(
            'Failed to fetch balance: ${response.body}', response.statusCode);
      }

      return response;
    } catch (e) {
      print("Error during balance fetch: $e");
      return http.Response('Error during balance fetch: $e', 500);
    }
  }

  // Récupérer les transactions de l'utilisateur
  Future<http.Response> fetchTransactions() async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$baseUrl/transactions'); // Utilisation de baseUrl

      var token = await storage.read(key: 'jwt');

      if (token == null) {
        return http.Response('Missing authorization token', 401);
      }

      var response = await client.get(uri, headers: {
        HttpHeaders.authorizationHeader: 'Bearer $token',
      });

      if (response.statusCode != 200) {
        return http.Response(
            'Failed to fetch transactions: ${response.body}', response.statusCode);
      }

      return response;
    } catch (e) {
      print("Error during transactions fetch: $e");
      return http.Response('Error during transactions fetch: $e', 500);
    }
  }

  // Récupérer le rôle de l'utilisateur stocké
  Future<String?> getRole() async {
    return await storage.read(key: 'role');
  }
}
