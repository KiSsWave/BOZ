import 'dart:convert';
import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

class RemoteService {
  final storage = const FlutterSecureStorage();
  static const String _baseUrl = 'http://192.168.1.66:44050';

  Future<http.Response> registerUser(String email, String username, String password) async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$_baseUrl/register');
      var response = await client.post(uri, body: {"email": email, "login": username, "password": password});
      if (response.statusCode != 200) {
        throw Exception('Failed to register user');
      }
      return response;
    } catch (e) {
      print("Error during registration: $e");
      return http.Response('Error during registration: $e', 500);
    }
  }

  Future<http.Response> loginUser(String username, String password) async {
    try {
      var client = http.Client();
      var uri = Uri.parse('$_baseUrl/signin');
      var response = await client.post(uri, body: {"email": username, "password": password});

      if (response.statusCode == 200) {
        var data = jsonDecode(response.body);
        await storage.write(key: 'jwt', value: data['token']);
        await storage.write(key: 'role', value: data['role']);
      } else {
        throw Exception('Failed to login');
      }

      return response;
    } catch (e) {
      print("Error during login: $e");
      return http.Response('Error during login: $e', 500);
    }
  }

  Future<void> disconnectUser() async {
    try {
      await storage.delete(key: 'jwt');
      await storage.delete(key: 'role');
    } catch (e) {
      print("Error during disconnect: $e");
    }
  }

  Future<http.Response> getBill(String id) async {
    return await _authenticatedGetRequest('$_baseUrl/factures/$id');
  }

  Future<bool> isConnected() async {
    try {
      return await storage.read(key: 'jwt') != null;
    } catch (e) {
      print("Error checking connection status: $e");
      return false;
    }
  }

  Future<http.Response> fetchBalance() async {
    return await _authenticatedGetRequest('$_baseUrl/balance');
  }

  Future<http.Response> fetchHistory() async {
    return await _authenticatedGetRequest('$_baseUrl/history');
  }

  Future<List<Map<String, dynamic>>> fetchTickets() async {
    var response = await _authenticatedGetRequest('$_baseUrl/tickets');
    if (response.statusCode == 200) {
      var data = jsonDecode(response.body)['Tickets'] as List;
      return data.map((ticket) => {
        'Id': ticket['Id'],
        'type': ticket['type'],
        'message': ticket['message'],
        'status': ticket['status'],
        'adminId': ticket['Id Admin'],
      }).toList();
    } else {
      print("Error fetching tickets: ${response.body}");
      return [];
    }
  }

  Future<http.Response> openTicket(String type, String message) async {
    return await _authenticatedPostRequest(
      '$_baseUrl/tickets',
      body: {"type": type, "message": message},
    );
  }

  Future<http.Response> fetchBills() async {
    return await _authenticatedGetRequest('$_baseUrl/factures');
  }

  Future<http.Response> createBill(String description, double amount) async {
    return await _authenticatedPostRequest(
      '$_baseUrl/facture',
      body: {"label": description, "tarif": amount.toString()},
    );
  }

  Future<int?> getRole() async {
    try {
      var roleString = await storage.read(key: 'role');
      return roleString != null ? int.parse(roleString) : null;
    } catch (e) {
      print("Error retrieving role: $e");
      return null;
    }
  }

  Future<http.Response> _authenticatedGetRequest(String url) async {
    try {
      var token = await storage.read(key: 'jwt');
      if (token == null) {
        return http.Response('Missing authorization token', 401);
      }

      var response = await http.get(Uri.parse(url), headers: {
        HttpHeaders.authorizationHeader: 'Bearer $token',
      });
      
      if (response.statusCode != 200) {
        throw Exception('Failed to fetch data from $url');
      }

      return response;
    } catch (e) {
      print("Error fetching data: $e");
      return http.Response('Error fetching data: $e', 500);
    }
  }

  Future<http.Response> _authenticatedPostRequest(String url, {required Map<String, String> body}) async {
    try {
      var token = await storage.read(key: 'jwt');
      if (token == null) {
        return http.Response('Missing authorization token', 401);
      }

      var response = await http.post(
        Uri.parse(url),
        headers: {
          HttpHeaders.authorizationHeader: 'Bearer $token',
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body,
      );

      if (response.statusCode != 200) {
        throw Exception('Failed to make POST request to $url');
      }

      return response;
    } catch (e) {
      print("Error making POST request: $e");
      return http.Response('Error making POST request: $e', 500);
    }
  }
}
