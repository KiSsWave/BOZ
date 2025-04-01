import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';

class AddBillPage extends StatefulWidget {
  const AddBillPage({Key? key}) : super(key: key);

  @override
  _AddBillPageState createState() => _AddBillPageState();
}

class _AddBillPageState extends State<AddBillPage> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _libelleController = TextEditingController();
  final TextEditingController _montantController = TextEditingController();
  final TextEditingController _destinataireController = TextEditingController();
  bool _isSubmitting = false;
  bool _isCheckingUser = false;
  String? _userError;
  List<Map<String, String>> _suggestedUsers = [];
  bool _isUserValid = false;

  Future<void> _submitBill() async {
    if (_formKey.currentState?.validate() ?? false) {
      if (_destinataireController.text.isNotEmpty && !_isUserValid) {
        setState(() {
          _userError = 'Cet utilisateur n\'existe pas';
        });
        return;
      }

      setState(() {
        _isSubmitting = true;
        _userError = null;
      });

      try {
        final response = await RemoteService().createBill(
          _libelleController.text,
          double.parse(_montantController.text),
          _destinataireController.text.isNotEmpty
              ? _destinataireController.text
              : null,
        );

        if (response.statusCode == 200 || response.statusCode == 201) {
          Navigator.pop(context, true);
        } else {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Erreur lors de la création de la facture')),
          );
          setState(() {
            _isSubmitting = false;
          });
        }
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
        setState(() {
          _isSubmitting = false;
        });
      }
    }
  }

  Future<void> _searchUsers(String query) async {
    if (query.length >= 3) {
      setState(() {
        _isCheckingUser = true;
      });

      try {
        final response = await RemoteService().searchUser(query);
        if (response.statusCode == 200) {
          var data = jsonDecode(response.body);
          // Fixed parsing logic
          List<Map<String, String>> users = (data['users'] as List)
              .map((user) => {
                    'login': user['login'] as String,
                    'email': user['email'] as String,
                  })
              .toList(); // Debugging line
          setState(() {
            _suggestedUsers = users;
            // If we found a match for the exact query, mark it as valid
            _isUserValid = users.any((user) => user['login'] == query);
          });
        }
      } catch (e) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content:
                  Text('Erreur lors de la recherche des utilisateurs: $e')),
        );
      } finally {
        setState(() {
          _isCheckingUser = false;
        });
      }
    } else {
      setState(() {
        _suggestedUsers = [];
        _isUserValid = false;
      });
    }
  }

  @override
  void dispose() {
    _libelleController.dispose();
    _montantController.dispose();
    _destinataireController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ajouter une Facture'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _libelleController,
                decoration: const InputDecoration(
                  labelText: 'Libellé',
                  border: OutlineInputBorder(),
                ),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Le libellé est obligatoire';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _montantController,
                decoration: const InputDecoration(
                  labelText: 'Montant (€)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.euro),
                ),
                keyboardType: TextInputType.numberWithOptions(decimal: true),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Le montant est obligatoire';
                  }
                  if (double.tryParse(value) == null) {
                    return 'Veuillez entrer un montant valide';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _destinataireController,
                decoration: InputDecoration(
                  labelText: 'Destinataire (optionnel)',
                  border: OutlineInputBorder(
                    borderSide: BorderSide(
                      color: _isUserValid ? Colors.green : Colors.grey,
                    ),
                  ),
                  errorText: _userError,
                  suffixIcon: _isCheckingUser
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : _isUserValid
                          ? const Icon(Icons.check_circle, color: Colors.green)
                          : null,
                ),
                onChanged: (value) {
                  if (_userError != null) {
                    setState(() {
                      _userError = null;
                      _isUserValid = false;
                    });
                  }
                  _searchUsers(value);
                },
              ),
              if (_suggestedUsers.isNotEmpty)
                Column(
                  children: _suggestedUsers.map((user) {
                    return ListTile(
                      title: Text(user['login']!),
                      subtitle: Text(user['email']!),
                      onTap: () async {
                        setState(() {
                          _destinataireController.text = user['login']!;
                          _suggestedUsers = [];
                          _isUserValid = true;
                        });
                      },
                    );
                  }).toList(),
                ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed:
                    (_isSubmitting || _isCheckingUser) ? null : _submitBill,
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
                icon: const Icon(Icons.save),
                label: _isSubmitting
                    ? const CircularProgressIndicator()
                    : const Text('Enregistrer la facture'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
