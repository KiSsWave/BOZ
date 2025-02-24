import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'chat_page.dart';
import 'faq_page.dart';

class HelpPage extends StatefulWidget {
  const HelpPage({Key? key}) : super(key: key);

  @override
  _HelpPageState createState() => _HelpPageState();
}

class _HelpPageState extends State<HelpPage> {
  List<Map<String, dynamic>> _tickets = [];
  bool _isLoading = false;
  final RemoteService _remoteService = RemoteService();

  @override
  void initState() {
    super.initState();
    _loadTickets();
  }

  Future<void> _loadTickets() async {
    setState(() => _isLoading = true);
    try {
      final tickets = await _remoteService.fetchTickets();
      setState(() {
        _tickets = tickets;
      });
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Erreur lors du chargement des tickets'),
          backgroundColor: Colors.red,
        ),
      );
    }
    setState(() => _isLoading = false);
  }

  void _openTicketForm() {
    String message = "";
    String? selectedType;

    showDialog(
      context: context,
      builder: (BuildContext context) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text("Ouvrir un ticket"),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    DropdownButtonFormField<String>(
                      decoration: const InputDecoration(
                        labelText: "Type de demande",
                        border: OutlineInputBorder(),
                      ),
                      value: selectedType,
                      items: ["Question", "Demande de fond", "Devenir Vendeur"]
                          .map((String type) {
                        return DropdownMenuItem<String>(
                          value: type,
                          child: Text(type),
                        );
                      }).toList(),
                      onChanged: (String? value) {
                        setState(() => selectedType = value);
                      },
                    ),
                    const SizedBox(height: 16),
                    TextField(
                      decoration: const InputDecoration(
                        labelText: "Message",
                        border: OutlineInputBorder(),
                      ),
                      maxLines: 3,
                      onChanged: (value) => message = value,
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("Annuler"),
                ),
                ElevatedButton(
                  onPressed: () async {
                    if (selectedType != null && message.isNotEmpty) {
                      try {
                        final response = await _remoteService.openTicket(selectedType!, message);
                        if (response.statusCode == 201) {
                          await _loadTickets();
                          Navigator.pop(context);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Ticket créé avec succès'),
                              backgroundColor: Colors.green,
                            ),
                          );
                        } else {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Erreur lors de la création du ticket'),
                              backgroundColor: Colors.red,
                            ),
                          );
                        }
                      } catch (e) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Erreur lors de la création du ticket'),
                            backgroundColor: Colors.red,
                          ),
                        );
                      }
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.black,
                  ),
                  child: const Text("Envoyer"),
                ),
              ],
            );
          },
        );
      },
    );
  }

  void _openChat(String ticketId, String status) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => ChatPage(ticketId: ticketId, status: status),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade300,
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadTickets,
              child: Container(
                padding: const EdgeInsets.all(16.0),
                child: Card(
                  color: Colors.white,
                  elevation: 2,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12.0),
                  ),
                  child: ListView(
                    padding: const EdgeInsets.all(8.0),
                    children: [
                      ListTile(
                        title: const Text(
                          'FAQ',
                          style: TextStyle(fontSize: 18.0, color: Colors.black),
                        ),
                        trailing: const Icon(
                          Icons.arrow_forward_ios,
                          color: Colors.black54,
                        ),
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(builder: (context) => FAQPage()),
                          );
                        },
                      ),
                      const Divider(color: Colors.black54),
                      ListTile(
                        title: const Text(
                          'Ouvrir un ticket',
                          style: TextStyle(fontSize: 18.0, color: Colors.black),
                        ),
                        trailing: const Icon(
                          Icons.add_circle_outline,
                          color: Colors.black,
                        ),
                        onTap: _openTicketForm,
                      ),
                      const Divider(color: Colors.black54),
                      const Padding(
                        padding: EdgeInsets.all(8.0),
                        child: Text(
                          "Mes Tickets",
                          style: TextStyle(
                            fontSize: 20.0,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      if (_tickets.isEmpty)
                        const Padding(
                          padding: EdgeInsets.all(16.0),
                          child: Center(
                            child: Text(
                              "Vous n'avez pas encore de tickets",
                              style: TextStyle(
                                fontSize: 16.0,
                                color: Colors.grey,
                              ),
                            ),
                          ),
                        )
                      else
                        ..._tickets.map(
                          (ticket) => Column(
                            children: [
                              ListTile(
                                leading: const Icon(Icons.chat, color: Colors.black),
                                title: Text(
                                  ticket['type'] ?? '',
                                  style: const TextStyle(
                                    fontSize: 18.0,
                                    color: Colors.black,
                                  ),
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      ticket['message'] ?? '',
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(color: Colors.black54),
                                    ),
                                    Text(
                                      "Statut: ${ticket['status'] ?? ''}",
                                      style: TextStyle(
                                        color: ticket['status'] == 'en attente'
                                            ? Colors.orange
                                            : ticket['status'] == 'en cours'
                                                ? Colors.blue
                                                : Colors.green,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                                trailing: const Icon(
                                  Icons.arrow_forward_ios,
                                  color: Colors.black54,
                                ),
                                onTap: () => _openChat(
                                  ticket['Id'].toString(),
                                  ticket['status'] ?? '',
                                ),
                              ),
                              const Divider(),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
    );
  }
}