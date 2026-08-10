import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:identity_wallet/main.dart';
import 'package:identity_wallet/navigation/app_router.dart';

void main() {
  testWidgets('splash screen renders brand', (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pump();

    expect(find.text('Identity Wallet'), findsOneWidget);
    expect(find.byIcon(Icons.shield_outlined), findsOneWidget);
  });

  testWidgets('login screen validates empty form', (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.login);
    await tester.pumpAndSettle();

    await tester.tap(find.widgetWithText(ElevatedButton, 'Sign In'));
    await tester.pump();

    expect(find.text('Email is required'), findsOneWidget);
    expect(find.text('Password is required'), findsOneWidget);
  });

  testWidgets('register screen validates password match',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester
        .state<NavigatorState>(find.byType(Navigator))
        .pushNamed(AppRoutes.register);
    await tester.pumpAndSettle();

    await tester.enterText(
      find.widgetWithText(TextFormField, 'Your name'),
      'Test User',
    );
    await tester.enterText(
      find.widgetWithText(TextFormField, 'you@example.com'),
      'test@example.com',
    );

    final passwordFields = find.byType(TextFormField);
    await tester.enterText(passwordFields.at(2), 'password123');
    await tester.enterText(passwordFields.at(3), 'different123');

    await tester.tap(find.widgetWithText(ElevatedButton, 'Create Account'));
    await tester.pump();

    expect(find.text('Passwords do not match'), findsOneWidget);
  });
}
