import 'dart:async';
import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'package:boz/pages/chat_page.dart';

class ConversationsPage extends StatefulWidget {
  const ConversationsPage({Key? key}) : super(key: key);

  @override
  _ConversationsPageState createState() => _ConversationsPageState();
}

class _ConversationsPageState extends State<ConversationsPage> {
  final RemoteService _remoteService = RemoteService();
  List<Map<String, dynamic>> _conversations = [];
  Timer? _refreshTimer;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchConversations();
    // Refresh conversations every 5 seconds
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      _fetchConversations();
    });
  }

  @override
  void dispose() {
    // Cancel the timer when the widget is disposed
    _refreshTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchConversations() async {
    try {
      var conversations = await _remoteService.fetchConversations();
      if (mounted) {
        setState(() {
          _conversations = conversations;
          _isLoading = false;
        });
      }
    } catch (e) {
      print("Error fetching conversations: $e");
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  String _getConversationTitle(Map<String, dynamic> conversation) {
    return conversation['user1Login'] ?? conversation['user2Login'] ?? 'Unknown';
  }

  Widget _buildConversationItem(BuildContext context, Map<String, dynamic> conversation) {
    return Card(
      elevation: 2,
      margin: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
      ),
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        leading: CircleAvatar(
          backgroundColor: Theme.of(context).colorScheme.primaryContainer,
          child: Text(
            _getConversationTitle(conversation)[0].toUpperCase(),
            style: TextStyle(
              color: Theme.of(context).colorScheme.onPrimaryContainer,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(
          _getConversationTitle(conversation),
          style: Theme.of(context).textTheme.titleMedium,
        ),
        subtitle: Text(
          conversation['lastMessage'] ?? 'No messages',
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: Colors.grey[600],
          ),
        ),
        trailing: Icon(
          Icons.chevron_right,
          color: Theme.of(context).colorScheme.secondary,
        ),
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => ChatPage(
                conversationId: conversation['id'].toString(),
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(),
            )
          : _conversations.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.chat_bubble_outline,
                        size: 80,
                        color: Colors.grey[300],
                      ),
                      const SizedBox(height: 16),
                      Text(
                        'No conversations yet',
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchConversations,
                  child: ListView.builder(
                    physics: const AlwaysScrollableScrollPhysics(),
                    itemCount: _conversations.length,
                    itemBuilder: (context, index) {
                      var conversation = _conversations[index];
                      return _buildConversationItem(context, conversation);
                    },
                  ),
                ),
    );
  }
}