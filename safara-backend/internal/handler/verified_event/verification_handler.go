package verified_event

import (
	"net/http"
	appVerifiedEvent "safara-backend/internal/application/verified_event"
	"safara-backend/pkg/response"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
)

type Handler struct {
	service appVerifiedEvent.Service
}

func NewHandler(service appVerifiedEvent.Service) *Handler {
	return &Handler{service: service}
}

type ApproveReportRequest struct {
	ReportID    string `json:"report_id" binding:"required"`
	ModeratorID string `json:"moderator_id" binding:"required"`
	Note        string `json:"note"`
}

func (h *Handler) ApproveReport(c *gin.Context) {
	var req ApproveReportRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid request payload", err.Error())
		return
	}

	reportID, err := uuid.Parse(req.ReportID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid report_id format", nil)
		return
	}

	moderatorID, err := uuid.Parse(req.ModeratorID)
	if err != nil {
		response.Error(c, http.StatusBadRequest, "Invalid moderator_id format", nil)
		return
	}

	result, err := h.service.ApproveReport(c.Request.Context(), reportID, moderatorID, req.Note)
	if err != nil {
		response.Error(c, http.StatusInternalServerError, "Failed to approve report", err.Error())
		return
	}

	response.Success(c, http.StatusOK, "Report approved and verified event created", result)
}
