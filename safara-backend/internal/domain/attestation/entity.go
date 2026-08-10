package attestation

import (
	"time"

	"github.com/google/uuid"
)

type Attestation struct {
	ID              uuid.UUID `gorm:"type:uuid;primary_key" json:"id"`
	VerifiedEventID uuid.UUID `gorm:"type:uuid;uniqueIndex;not null" json:"verified_event_id"`
	Chain           string    `gorm:"type:varchar(50);not null" json:"chain"`
	Network         string    `gorm:"type:varchar(50);not null" json:"network"`
	AttestationID   string    `gorm:"type:varchar(255);uniqueIndex" json:"attestation_id"`
	TxHash          string    `gorm:"type:varchar(255)" json:"tx_hash"`
	Status          string    `gorm:"type:varchar(20);default:'pending'" json:"status"`
	AttestedAt      time.Time `json:"attested_at"`
}

func (Attestation) TableName() string {
	return "attestations"
}
