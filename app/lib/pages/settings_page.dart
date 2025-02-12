import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart'; // Assurez-vous d'importer votre RemoteService

class SettingsPage extends StatefulWidget {
  const SettingsPage({Key? key}) : super(key: key);
  @override
  _SettingsPageState createState() => _SettingsPageState();
}

class _SettingsPageState extends State<SettingsPage> {
  bool _notificationsEnabled = true;
  bool _darkModeEnabled = false;

  void _confirmLogout() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Déconnexion"),
        content: const Text("Êtes-vous sûr de vouloir vous déconnecter ?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text("Annuler"),
          ),
          TextButton(
            onPressed: () async {
              Navigator.of(context).pop();
              RemoteService remoteService = RemoteService();
              await remoteService.disconnectUser();
              Navigator.pushReplacementNamed(context, "/login");
            },
            child: const Text("Confirmer", style: TextStyle(color: Colors.red)),
          ),
        ],
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
            children:  [
              SwitchListTile(
                title: const Text('Activer les notifications', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                value: _notificationsEnabled,
                activeColor: Colors.black,
                onChanged: (value) {
                  setState(() {
                    _notificationsEnabled = value;
                  });
                },
              ),
              Divider(color: Colors.black54),
              SwitchListTile(
                title: const Text('Mode sombre', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                value: _darkModeEnabled,
                activeColor: Colors.black,
                onChanged: (value) {
                  setState(() {
                    _darkModeEnabled = value;
                  });
                },
              ),
              Divider(color: Colors.black54),
              ListTile(
                leading: Icon(Icons.account_balance, color: Colors.black),
                title: const Text('Transférer vers un compte bancaire', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                trailing: const Icon(Icons.arrow_forward_ios, color: Colors.black54),
                onTap: () {},
              ),
              Divider(color: Colors.black54),
              ListTile(
                leading: Icon(Icons.account_circle, color: Colors.black),
                title: const Text('Gérer le compte', style: TextStyle(fontSize: 18.0, color: Colors.black)),
                trailing: const Icon(Icons.arrow_forward_ios, color: Colors.black54),
                onTap: () {},
              ),
              Divider(color: Colors.black54),
              ListTile(
                leading: Icon(Icons.logout, color: Colors.red),
                title: const Text('Se déconnecter', style: TextStyle(fontSize: 18.0, color: Colors.red)),
                trailing: const Icon(Icons.arrow_forward_ios, color: Colors.red),
                onTap: _confirmLogout,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
