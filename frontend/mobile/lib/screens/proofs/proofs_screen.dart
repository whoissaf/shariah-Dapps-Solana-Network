import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/empty_state.dart';

class ProofsScreen extends StatelessWidget {
  const ProofsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Proofs'),
        backgroundColor: AppColors.background,
        elevation: 0,
      ),
      body: const SafeArea(
        child: EmptyState(
          icon: Icons.verified_user_outlined,
          title: 'No proofs yet',
          subtitle: 'Generate your first zero-knowledge proof from a claim.',
        ),
      ),
    );
  }
}
