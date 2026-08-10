package report

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type Report struct {
	ID          uuid.UUID      `gorm:"type:uuid;primary_key" json:"id"`
	UserID      uuid.UUID      `gorm:"type:uuid;not null" json:"user_id"`
	LocationID  uuid.UUID      `gorm:"type:uuid;not null" json:"location_id"`
	Category    string         `gorm:"type:varchar(50);not null" json:"category"`
	Title       string         `gorm:"type:varchar(255);not null" json:"title"`
	Description string         `gorm:"type:text;not null" json:"description"`
	Status      string         `gorm:"type:varchar(20);default:'submitted'" json:"status"`
	SubmittedAt time.Time      `json:"submitted_at"`
	CreatedAt   time.Time      `json:"created_at"`
	UpdatedAt   time.Time      `json:"updated_at"`
	DeletedAt   gorm.DeletedAt `gorm:"index" json:"-"`
}

func (Report) TableName() string {
	return "reports"
}

type ReportMedia struct {
	ID        uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	ReportID  uuid.UUID `gorm:"type:uuid;not null" json:"report_id"`
	FileURL   string    `gorm:"type:varchar(500);not null" json:"file_url"`
	MimeType  string    `gorm:"type:varchar(100);not null" json:"mime_type"`
	CreatedAt time.Time `json:"created_at"`
}

func (ReportMedia) TableName() string {
	return "report_media"
}
