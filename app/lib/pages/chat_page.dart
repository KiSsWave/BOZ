import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:boz/services/remote_service.dart';

class ChatPage extends StatefulWidget {
  final String conversationId;

  const ChatPage({Key? key, required this.conversationId}) : super(key: key);

  @override
  _ChatPageState createState() => _ChatPageState();
}

class _ChatPageState extends State<ChatPage> {
  final RemoteService _remoteService = RemoteService();
  final TextEditingController _messageController = TextEditingController();
  List<Map<String, dynamic>> _messages = [];
  Map<String, dynamic> _conversationInfo = {};
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    _fetchMessages();
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (timer) {
      _fetchMessages();
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _fetchMessages() async {
    try {
      var conversationData =
          await _remoteService.fetchConversationMessages(widget.conversationId);
      if (mounted) {
        setState(() {
          _messages = conversationData['messages'];
          _conversationInfo = conversationData['conversation'];
        });
      }
    } catch (e) {
      print("Error fetching messages: $e");
    }
  }

  Future<void> _sendMessage() async {
    if (_messageController.text.isNotEmpty) {
      // Create a local message to add immediately
      final localMessage = {
        'content': _messageController.text,
        'isMine': true,
        'timestamp': DateTime.now().millisecondsSinceEpoch ~/ 1000,
      };

      // Optimistically add the message to the local list
      setState(() {
        _messages.insert(0, localMessage);
      });

      // Clear the input field immediately
      _messageController.clear();

      // Try to send the message via API
      bool success = await _remoteService.sendMessage(
          widget.conversationId, localMessage['content'] as String);

      if (success) {
        // Refresh messages to get the server-confirmed message
        _fetchMessages();
      } else {
        // If sending fails, remove the local message
        setState(() {
          _messages.removeAt(0);
        });
      }
    }
  }

  DateTime _parseTimestamp(dynamic timestamp) {
    if (timestamp is int) {
      return DateTime.fromMillisecondsSinceEpoch(timestamp * 1000);
    } else if (timestamp is String) {
      try {
        return DateTime.parse(timestamp);
      } catch (_) {
        return DateTime.now();
      }
    }
    return DateTime.now();
  }

  String _formatTimestamp(dynamic timestamp) {
    DateTime dateTime = _parseTimestamp(timestamp);
    return DateFormat('dd MMM yyyy, HH:mm').format(dateTime);
  }

  Map<String, List<Map<String, dynamic>>> _groupMessagesByDate() {
    Map<String, List<Map<String, dynamic>>> groupedMessages = {};
    for (var message in _messages) {
      DateTime messageDate = _parseTimestamp(message['timestamp']);
      String dateKey = DateFormat('dd MMM yyyy').format(messageDate);
      groupedMessages.putIfAbsent(dateKey, () => []).add(message);
    }
    return groupedMessages;
  }

  @override
  Widget build(BuildContext context) {
    final groupedMessages = _groupMessagesByDate();
    final sortedDates = groupedMessages.keys.toList().reversed.toList();

    return Scaffold(
      backgroundColor: Colors.grey[50],
      appBar: AppBar(
        title: Text(
          "Conversation with ${_conversationInfo['user1Login'] ?? _conversationInfo['user2Login'] ?? 'User'}",
          style:
              const TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: Colors.black,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              reverse: true,
              itemCount: sortedDates.length,
              itemBuilder: (context, dateIndex) {
                String currentDate = sortedDates[dateIndex];
                List<Map<String, dynamic>> dailyMessages =
                    groupedMessages[currentDate]!;
                return Column(
                  children: [
                    Container(
                      margin: const EdgeInsets.symmetric(vertical: 8),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 12, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.grey[300],
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        currentDate,
                        style: TextStyle(
                            color: Colors.grey[700],
                            fontWeight: FontWeight.w500),
                      ),
                    ),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: dailyMessages.length,
                      itemBuilder: (context, messageIndex) {
                        final message = dailyMessages[messageIndex];
                        return Align(
                          alignment: message['isMine']
                              ? Alignment.centerRight
                              : Alignment.centerLeft,
                          child: Container(
                            margin: const EdgeInsets.symmetric(
                                vertical: 4, horizontal: 12),
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: message['isMine']
                                  ? Colors.grey[300]
                                  : Colors.grey[200],
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  message['content'],
                                  style: const TextStyle(
                                      fontSize: 16, color: Colors.black87),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  _formatTimestamp(message['timestamp']),
                                  style: TextStyle(
                                      fontSize: 12, color: Colors.grey[600]),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                );
              },
            ),
          ),
          Container(
            padding: const EdgeInsets.all(8.0),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _messageController,
                    decoration: InputDecoration(
                      hintText: 'Type a message...',
                      filled: true,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(30),
                        borderSide: BorderSide.none,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                FloatingActionButton(
                  backgroundColor: Colors.black,
                  onPressed: _sendMessage,
                  child: const Icon(Icons.send, color: Colors.white),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
