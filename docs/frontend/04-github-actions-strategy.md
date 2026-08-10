GITHUB ACTIONS STRATEGY

Flutter Build
Trigger
push to main when frontend/mobile changes

Steps
checkout
setup java
setup flutter
generate android platform
flutter pub get
flutter analyze
flutter test
flutter build apk
upload apk artifact

Next Build
Trigger
push to main when frontend/web changes

Steps
checkout
setup node
npm install
next build
upload static export artifact

Artifact Names
identity-wallet-apk
verifier-web-static
