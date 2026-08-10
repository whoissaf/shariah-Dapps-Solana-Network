package location

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type Country struct {
	ID        uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	Name      string    `gorm:"type:varchar(100);not null" json:"name"`
	IsoCode   string    `gorm:"type:varchar(10);uniqueIndex" json:"iso_code"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

func (Country) TableName() string {
	return "countries"
}

type Province struct {
	ID        uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	CountryID uuid.UUID `gorm:"type:uuid;not null" json:"country_id"`
	Name      string    `gorm:"type:varchar(100);not null" json:"name"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

func (Province) TableName() string {
	return "provinces"
}

type City struct {
	ID         uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	ProvinceID uuid.UUID `gorm:"type:uuid;not null" json:"province_id"`
	Name       string    `gorm:"type:varchar(100);not null" json:"name"`
	CreatedAt  time.Time `json:"created_at"`
	UpdatedAt  time.Time `json:"updated_at"`
}

func (City) TableName() string {
	return "cities"
}

type Location struct {
	ID        uuid.UUID      `gorm:"type:uuid;primary_key" json:"id"`
	CityID    uuid.UUID      `gorm:"type:uuid;not null" json:"city_id"`
	Name      string         `gorm:"type:varchar(255);not null" json:"name"`
	Latitude  float64        `gorm:"type:decimal(10,8);not null" json:"latitude"`
	Longitude float64        `gorm:"type:decimal(11,8);not null" json:"longitude"`
	OsmID     string         `gorm:"type:varchar(100);uniqueIndex" json:"osm_id"`
	CreatedAt time.Time      `json:"created_at"`
	UpdatedAt time.Time      `json:"updated_at"`
	DeletedAt gorm.DeletedAt `gorm:"index" json:"-"`
}

func (Location) TableName() string {
	return "locations"
}
