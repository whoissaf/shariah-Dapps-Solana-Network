package report

import (
	"net/http"
	"safara-backend/internal/application/report"
	"safara-backend/pkg/response"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Handler struct {
	service report.Service
}

func NewHandler(service report.Service) *Handler {
	return &Handler{service: service}
}

type SubmitReportRequest struct {
	UserID      string `json:"user_id" binding:"required"`
	LocationID  string `json:"location_id" binding:"required"`
	Category    string `json:"category" binding:"required"`
	Title       string `json:"title" binding:"required"`
	Description string `json:"description" binding:"required"`
}

func (h *Handler) SubmitReport(c *gin.Context) {
	var req SubmitReportRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload", err.Error())
		return
	}

	userID, err := uuid.Parse(req.UserID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid user_id format", nil)
		return
	}

	locationID, err := uuid.Parse(req.LocationID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid location_id format", nil)
		return
	}

	appReq := report.SubmitReportRequest{
		UserID:      userID,
		LocationID:  locationID,
		Category:    req.Category,
		Title:       req.Title,
		Description: req.Description,
	}

	result, err := h.service.SubmitReport(c.Request.Context(), appReq)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to submit report", err.Error())
		return
	}

	response.Success(c, http.StatusCreated, "Report submitted successfully", result)
}
