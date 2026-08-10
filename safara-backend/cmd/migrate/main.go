package main

import (
	"log"
	"safara-backend/internal/config"
	"safara-backend/internal/domain/ai"
	"safara-backend/internal/domain/attestation"
	"safara-backend/internal/domain/location"
	"safara-backend/internal/domain/report"
	"safara-backend/internal/domain/rule_engine"
	"safara-backend/internal/domain/system"
	"safara-backend/internal/domain/user"
	"safara-backend/internal/domain/verified_event"
	"safara-backend/pkg/database"
)

func main() {
	cfg := config.LoadConfig()
	db := database.NewPostgresDB(cfg)

	err := db.AutoMigrate(
		&user.Role{},
		&user.User{},
		&user.UserSession{},
		&location.Country{},
		&location.Province{},
		&location.City{},
		&location.Location{},
		&report.Report{},
		&report.ReportMedia{},
		&verified_event.VerificationRequest{},
		&verified_event.VerificationLog{},
		&verified_event.VerifiedEvent{},
		&rule_engine.RuleSnapshot{},
		&rule_engine.RuleReason{},
		&ai.AIExplanation{},
		&attestation.Attestation{},
		&system.AuditLog{},
	)

	if err != nil {
		log.Fatalf("Migration failed: %v", err)
	}

	log.Println("Migration completed successfully")
}
