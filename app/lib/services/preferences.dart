import 'dart:convert';
import 'package:boz/models/transaction.dart';
import 'package:shared_preferences/shared_preferences.dart';

class Preferences {
  static const String balanceKey = 'balance';
  static const String lastUpdateKey = 'lastUpdate';
  static const String transactionsKey = 'transactions';

  // Singleton instance
  static final Preferences _instance = Preferences._internal();
  factory Preferences() => _instance;
  Preferences._internal();

  SharedPreferences? _prefs;

  /// Initialiser les préférences partagées
  Future<void> init() async {
    _prefs ??= await SharedPreferences.getInstance();
  }

  /// Vérifie si les préférences sont initialisées
  void _ensurePrefsInitialized() {
    assert(_prefs != null, "Preferences is not initialized. Call init() first.");
  }

  /// Récupère le solde actuel
  double getBalance() {
    _ensurePrefsInitialized();
    return _prefs?.getDouble(balanceKey) ?? 0.0;
  }

  /// Récupère la dernière date de mise à jour
  String getLastUpdate() {
    _ensurePrefsInitialized();
    return _prefs?.getString(lastUpdateKey) ?? "";
  }

  /// Récupère la liste des transactions
  List<Transaction> getTransactions() {
    _ensurePrefsInitialized();
    try {
      return (_prefs?.getStringList(transactionsKey) ?? [])
          .map((item) => Transaction.fromJson(jsonDecode(item)))
          .toList();
    } catch (e) {
      print("Error while retrieving transactions: $e");
      return [];
    }
  }

  /// Définit le solde actuel
  Future<void> setBalance(double balance) async {
    _ensurePrefsInitialized();
    await _prefs?.setDouble(balanceKey, balance);
  }

  /// Définit la dernière date de mise à jour
  Future<void> setLastUpdate(String lastUpdate) async {
    _ensurePrefsInitialized();
    await _prefs?.setString(lastUpdateKey, lastUpdate);
  }

  /// Définit la liste des transactions
  Future<void> setTransactions(List<Transaction> transactions) async {
    _ensurePrefsInitialized();
    try {
      final encodedTransactions =
          transactions.map((t) => jsonEncode(t.toJson())).toList();
      await _prefs?.setStringList(transactionsKey, encodedTransactions);
    } catch (e) {
      print("Error while saving transactions: $e");
    }
  }

}
