import 'dart:typed_data';
import 'package:intl/intl.dart';
import 'dart:convert';

class Bill {
  final String id;
  final String libelle;
  final double montant;
  final DateTime? date;
  final String? buyer_login;
  final String? seller_login;
  final String? status;
  final Uint8List? qrCode;

  Bill({
    required this.id,
    required this.libelle,
    required this.montant,
    this.date,
    this.status,
    this.buyer_login,
    this.seller_login,
    this.qrCode,
  });

  factory Bill.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate() {
      if (json['date'] == null || json['date'].toString().isEmpty) {
        return null;
      }
      try {
        // Si la date est au format ISO
        return DateTime.parse(json['date']);
      } catch (e) {
        try {
          // Si la date est au format français
          return DateFormat('dd/MM/yyyy').parse(json['date']);
        } catch (e) {
          return null;
        }
      }
    }

    Uint8List? parseQrCode() {
      if (json['qr_code'] == null || json['qr_code'].toString().isEmpty) {
        return null;
      }
      try {
        return base64Decode(json['qr_code']);
      } catch (e) {
        return null;
      }
    }

    return Bill(
      id: json['id'],
      libelle: json['label'],
      montant: double.parse(json['amount'].toString()),
      date: parseDate(),
      buyer_login: json['buyer_login'],
      seller_login: json['seller_login'],
      status: json['status'],
      qrCode: parseQrCode(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'label': libelle,
      'montant': montant,
      'created_at': date?.toIso8601String(),
      'buyer_login': buyer_login,
      'seller_login': seller_login,
      'status': status,
      'qrCode': qrCode != null ? base64Encode(qrCode!) : null,
    };
  }
}
