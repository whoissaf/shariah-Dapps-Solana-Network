package main

import (
	"fmt"
	"log"
	"safara-backend/internal/config"
	appAI "safara-backend/internal/application/ai"
	appAttestation "safara-backend/internal/application/attestation"
	appAuth "safara-backend/internal/application/auth"
	appReport "safara-backend/internal/application/report"
	appVerifiedEvent "safara-backend/internal/application/verified_event"
	handlerAI "safara-backend/internal/handler/ai"
	handlerAttestation "safara-backend/internal/handler/attestation"
	handlerAuth "safara-backend/internal/handler/auth"
	handlerReport "safara-backend/internal/handler/report"
	handlerVerifiedEvent "safara-backend/internal/handler/verified_event"
	"safara-backend/internal/middleware"
	repoAI "safara-backend/internal/repository/ai"
	repoAttestation "safara-backend/internal/repository/attestation"
	repoReport "safara-backend/internal/repository/report"
	repoRuleEngine "safara-backend/internal/repository/rule_engine"
	repoUser "safara-backend/internal/repository/user"
	repoVerifiedEvent "safara-backend/internal/repository/verified_event"
	"safara-backend/internal/domain/rule_engine"
	"safara-backend/internal/domain/user"
	"safara-backend/pkg/database"

	"github.com/gin-contrib/cors"
	"github.com/gin-gonic/gin"
)

func main() {
	cfg := config.LoadConfig()
	db := database.NewPostgresDB(cfg)

	sqlDB, err := db.DB()
	if err != nil {
		log.Fatalf("failed to get db instance: %v", err)
	}
	defer sqlDB.Close()

	db.AutoMigrate(&user.User{})

	r := gin.Default()

	r.Use(cors.New(cors.Config{
		AllowOrigins:     []string{"*"},
		AllowMethods:     []string{"GET", "POST", "PUT", "DELETE", "OPTIONS"},
		AllowHeaders:     []string{"Origin", "Content-Type", "Authorization"},
		ExposeHeaders:    []string{"Content-Length"},
		AllowCredentials: true,
	}))

	r.GET("/health", func(c *gin.Context) {
		c.JSON(200, gin.H{"success": true, "message": "Safara Backend is running"})
	})

	userRepo := repoUser.NewRepository(db)
	authService := appAuth.NewService(userRepo, cfg.JWTSecret)
	authHandler := handlerAuth.NewHandler(authService)

	authGroup := r.Group("/api/v1/auth")
	{
		authGroup.POST("/register", authHandler.Register)
		authGroup.POST("/login", authHandler.Login)
	}

	reportRepo := repoReport.NewReportRepository(db)
	reportService := appReport.NewService(reportRepo)
	reportHandler := handlerReport.NewHandler(reportService)

	verifiedEventRepo := repoVerifiedEvent.NewRepository(db)
	ruleEngine := rule_engine.NewEngine()
	ruleEngineRepo := repoRuleEngine.NewRepository(db)
	verifiedEventService := appVerifiedEvent.NewService(reportRepo, verifiedEventRepo, ruleEngine)
	verifiedEventHandler := handlerVerifiedEvent.NewHandler(verifiedEventService)

	attestationRepo := repoAttestation.NewRepository(db)
	attestationService := appAttestation.NewService(attestationRepo, verifiedEventRepo)
	attestationHandler := handlerAttestation.NewHandler(attestationService)

	aiRepo := repoAI.NewRepository(db)
	aiService := appAI.NewService(aiRepo, ruleEngineRepo, verifiedEventRepo)
	aiHandler := handlerAI.NewHandler(aiService)

	api := r.Group("/api/v1")
	api.Use(middleware.JWTAuth(cfg.JWTSecret))
	{
		api.POST("/reports", reportHandler.SubmitReport)
		api.POST("/moderator/approve", verifiedEventHandler.ApproveReport)
		api.POST("/attestations", attestationHandler.AttestEvent)
		api.POST("/ai/explain", aiHandler.GenerateExplanation)
	}

	addr := fmt.Sprintf(":%s", cfg.ServerPort)
	log.Printf("Server starting on %s", addr)
	if err := r.Run(addr); err != nil {
		log.Fatalf("failed to start server: %v", err)
	}
}
