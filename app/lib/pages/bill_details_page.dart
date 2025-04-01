import 'package:flutter/material.dart';
import 'package:boz/models/bill.dart';
import 'package:intl/intl.dart';
import 'dart:typed_data';
import 'dart:convert';
import 'package:boz/services/remote_service.dart'; // Ajout de l'import du service

class BillDetailPage extends StatefulWidget {
  // Changement en StatefulWidget
  final Bill bill;

  const BillDetailPage({Key? key, required this.bill}) : super(key: key);

  @override
  State<BillDetailPage> createState() => _BillDetailPageState();
}

class _BillDetailPageState extends State<BillDetailPage> {
  final RemoteService _remoteService = RemoteService();
  bool _isLoading = false;
  bool _canPay = false;

  @override
  void initState() {
    super.initState();
    _checkUserRole();
  }

  Future<void> _checkUserRole() async {
    int? userRole = await _remoteService.getRole();
    setState(() {
      _canPay =
          userRole == 1 && widget.bill.status?.toLowerCase() == "non payée";
    });
  }

  Future<void> _payBill() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await _remoteService.payBill(widget.bill.id);

      if (response.statusCode == 200 || response.statusCode == 201) {
        // Mise à jour du statut après paiement réussi
        setState(() {
          _canPay = false;
          _isLoading = false;
        });

        // Afficher un snackbar pour confirmation
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Paiement effectué avec succès !'),
              backgroundColor: Colors.green,
            ),
          );
        }
      } else {
        // Gérer le cas où la réponse n'est pas 200 ou 201
        setState(() {
          _isLoading = false;
        });

        // Récupérer le message d'erreur depuis la réponse si disponible
        String errorMessage;
        try {
          var responseData = jsonDecode(response.body);
          errorMessage = responseData['message'] ??
              'Erreur de paiement (${response.statusCode})';
        } catch (e) {
          errorMessage = 'Erreur de paiement (${response.statusCode})';
        }

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(errorMessage),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
      });

      // Afficher un message d'erreur
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur lors du paiement: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    // Formatage de la date
    final dateFormat = DateFormat('dd/MM/yyyy');
    final formattedDate = widget.bill.date != null
        ? dateFormat.format(widget.bill.date!)
        : 'Non spécifiée';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Détails de la facture'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              elevation: 2,
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildQrCode(context, widget.bill.qrCode!),
                    Text(
                      widget.bill.libelle,
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const Divider(),
                    _buildInfoRow(context, 'Montant',
                        '${widget.bill.montant.toStringAsFixed(2)} €'),
                    _buildInfoRow(context, 'Date', formattedDate),
                    if (widget.bill.status != null)
                      _buildStatusBadge(context, widget.bill.status!),

                    // Bouton de paiement
                    if (_canPay)
                      Padding(
                        padding: const EdgeInsets.only(top: 24.0),
                        child: Center(
                          child: ElevatedButton.icon(
                            onPressed: _isLoading ? null : _payBill,
                            icon: _isLoading
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Colors.white,
                                    ),
                                  )
                                : const Icon(Icons.payment),
                            label: Text(_isLoading
                                ? 'Traitement en cours...'
                                : 'Payer cette facture'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.green,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 20, vertical: 12),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(BuildContext context, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              '$label:',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 16),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(BuildContext context, String status) {
    Color backgroundColor;
    String label;

    switch (status.toLowerCase()) {
      case 'payée':
        backgroundColor = Colors.green;
        label = 'Payée';
        break;
      case "non payée":
        backgroundColor = Colors.red;
        label = 'Non payée';
        break;
      default:
        backgroundColor = Colors.blue;
        label = status;
    }

    return Center(
      child: Padding(
        padding: const EdgeInsets.only(top: 16.0),
        child: Chip(
          backgroundColor: backgroundColor.withOpacity(0.2),
          label: Text(
            label,
            style: TextStyle(
              color: backgroundColor,
              fontWeight: FontWeight.bold,
            ),
          ),
          avatar: Icon(
            status.toLowerCase() == 'payée'
                ? Icons.check_circle
                : Icons.schedule,
            color: backgroundColor,
            size: 18,
          ),
        ),
      ),
    );
  }

  Widget _buildQrCode(BuildContext context, Uint8List qrCode) {
    return Center (
    child: Padding(
      padding: const EdgeInsets.only(top: 16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'QR Code:',
            style: TextStyle(
              fontWeight: FontWeight.bold,
              color: Colors.grey,
            ),
          ),
          const SizedBox(height: 8.0),
          Image.memory(qrCode),
        ],
      ),
    ),);
  }
}
