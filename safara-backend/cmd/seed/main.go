package main

import (
	"log"
	"safara-backend/internal/config"
	"safara-backend/internal/domain/location"
	"safara-backend/internal/domain/user"
	"safara-backend/pkg/database"

	"github.com/google/uuid"
	"golang.org/x/crypto/bcrypt"
)

func main() {
	cfg := config.LoadConfig()
	db := database.NewPostgresDB(cfg)

	var roleCount int64
	db.Model(&user.Role{}).Count(&roleCount)
	if roleCount == 0 {
		role := user.Role{
			ID:          uuid.New(),
			Name:        "Traveler",
			Description: "Standard user",
		}
		db.Create(&role)

		hashedPassword, _ := bcrypt.GenerateFromPassword([]byte("password123"), bcrypt.DefaultCost)
		testUser := user.User{
			ID:           uuid.New(),
			RoleID:       role.ID,
			Fullname:     "Test User",
			Email:        "test@safara.com",
			PasswordHash: string(hashedPassword),
			Status:       "active",
		}
		db.Create(&testUser)

		country := location.Country{
			ID:      uuid.New(),
			Name:    "Indonesia",
			IsoCode: "ID",
		}
		db.Create(&country)

		province := location.Province{
			ID:        uuid.New(),
			CountryID: country.ID,
			Name:      "DKI Jakarta",
		}
		db.Create(&province)

		city := location.City{
			ID:         uuid.New(),
			ProvinceID: province.ID,
			Name:       "Jakarta Selatan",
		}
		db.Create(&city)

		testLocation := location.Location{
			ID:        uuid.New(),
			CityID:    city.ID,
			Name:      "Monas",
			Latitude:  -6.175392,
			Longitude: 106.827153,
			OsmID:     "123456",
		}
		db.Create(&testLocation)

		log.Println("Seed data created successfully")
		log.Printf("User ID: %s", testUser.ID)
		log.Printf("Location ID: %s", testLocation.ID)
	} else {
		log.Println("Seed data already exists")
	}
}
