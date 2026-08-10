import 'package:flutter/material.dart';
import '../../design/theme.dart';
import '../../design/components/empty_state.dart';

class CreateClaimScreen extends StatelessWidget {
  const CreateClaimScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Create Claim'),
        backgroundColor: AppColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: AppColors.textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: const SafeArea(
        child: EmptyState(
          icon: Icons.add_circle_outline,
          title: 'Stepper Coming Next',
          subtitle:
              'Claim creation stepper (Choose → Input → Review → Submit) will be implemented in the next stage.',
        ),
      ),
    );
  }
}
