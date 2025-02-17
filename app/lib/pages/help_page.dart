import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'chat_page.dart';

class HelpPage extends StatefulWidget {
  const HelpPage({Key? key}) : super(key: key);

  @override
  _HelpPageState createState() => _HelpPageState();
}

class _HelpPageState extends State<HelpPage> {
  List<Map<String, dynamic>> _tickets = [];

  @override
  void initState() {
    super.initState();
    _loadTickets();
  }

  void _loadTickets() async {
    List<Map<String, dynamic>> tickets = await RemoteService().fetchTickets();
    setState(() {
      _tickets = tickets;
    });
  }

  void _openTicketForm() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        String message = "";
        String? selectedType;
        return AlertDialog(
          title: const Text("Ouvrir un ticket"),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                decoration: const InputDecoration(labelText: "Type de demande"),
                items: ["Question", "Demande de fond", "Devenir Vendeur"].map((String type) {
                  return DropdownMenuItem<String>(
                    value: type,
                    child: Text(type),
                  );
                }).toList(),
                onChanged: (value) {
                  selectedType = value;
                },
              ),
              TextField(
                decoration: const InputDecoration(labelText: "Message"),
                maxLines: 3,
                onChanged: (value) {
                  message = value;
                },
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop();
              },
              child: const Text("Annuler"),
            ),
            ElevatedButton(
              onPressed: () {
                if (selectedType != null && message.isNotEmpty) {
                  RemoteService().openTicket(selectedType!, message);
                  _loadTickets();
                  Navigator.of(context).pop();
                }
              },
              child: const Text("Envoyer"),
            ),
          ],
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
      body: Container(
        padding: const EdgeInsets.all(16.0),
        child: Card(
          color: Colors.white,
          elevation: 2,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12.0)),
          child: ListView(
            padding: const EdgeInsets.all(8.0),
            children: [
              ListTile(
                title: const Text('FAQ', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                trailing: const Icon(Icons.arrow_forward_ios, color: Colors.black54),
                onTap: () {},
              ),
              Divider(color: Colors.black54),
              ListTile(
                title: const Text('Ouvrir un ticket', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                trailing: const Icon(Icons.add_circle_outline, color: Colors.black),
                onTap: _openTicketForm,
              ),
              Divider(color: Colors.black54),
              const Padding(
                padding: EdgeInsets.all(8.0),
                child: Text("Mes Tickets", style: TextStyle(fontSize: 20.0, fontWeight: FontWeight.bold)),
              ),
              ..._tickets.map((ticket) => ListTile(
                    leading: Icon(Icons.chat, color: Colors.black),
                    title: Text(ticket['type'], style: const TextStyle(fontSize: 18.0, color: Colors.black)),
                    subtitle: Text("Statut: ${ticket['status']}", style: const TextStyle(color: Colors.black54)),
                    trailing: const Icon(Icons.arrow_forward_ios, color: Colors.black54),
                    onTap: () => _openChat(ticket['Id'], ticket['status']),
                  )),
            ],
          ),
        ),
      ),
    );
  }
}
