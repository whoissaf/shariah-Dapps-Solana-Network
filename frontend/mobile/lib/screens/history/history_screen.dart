import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/empty_state.dart';

class HistoryScreen extends StatelessWidget {
  const HistoryScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('History'),
        backgroundColor: AppColors.background,
        elevation: 0,
      ),
      body: const SafeArea(
        child: EmptyState(
          icon: Icons.history,
          title: 'Verification Timeline',
          subtitle: 'Your proof verification history will appear here.',
        ),
      ),
    );
  }
}
