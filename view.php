<?php
// view.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mfj_db";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


if (!isset($_GET['id'])) {
    echo "No service ID provided.";
    exit;
}
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Service not found.";
} else {
    $service = $result->fetch_assoc();
    ?>
    <div class="modal-content shadow-lg border-0">
        <div class="modal-header border-0 position-relative py-4">
            <div class="position-absolute start-0 top-0 w-100 header-accent"></div>
            <h5 class="modal-title fw-bold px-2">Service Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 pb-4 pt-2">
            <!-- Service Status Banner -->
            <div class="status-banner mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="status-indicator <?= strtolower($service['status']) ?>"></span>
                        <span class="ms-2 fw-medium"><?= htmlspecialchars($service['status']) ?></span>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-dark fs-4">₱<?= htmlspecialchars($service['price']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($service['duration']) ?> hrs</div>
                    </div>
                </div>
            </div>
            
            <!-- Details Cards -->
            <div class="row g-4">
                <!-- Service Card -->
                <div class="col-md-6">
                    <div class="info-card service-card">
                        <div class="card-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <h6 class="card-title">Service</h6>
                        <div class="card-content">
                            <?php if (!empty($service['description'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Description</div>
                                    <div class="info-value"><?= htmlspecialchars($service['description']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-row">
                                <div class="info-label">Type</div>
                                <div class="info-value">
                                    <span class="type-pill"><?= htmlspecialchars($service['service_type']) ?></span>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Units</div>
                                <div class="info-value"><?= htmlspecialchars($service['number_of_units']) ?></div>
                            </div>
                            
                            <?php if (!empty($service['overtime_hours']) && $service['overtime_hours'] > 0): ?>
                                <div class="info-row">
                                    <div class="info-label">Overtime</div>
                                    <div class="info-value"><?= htmlspecialchars($service['overtime_hours']) ?> hrs</div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($service['evaluation_status'])): ?>
                                <div class="info-row">
                                    <div class="info-label">Evaluation</div>
                                    <div class="info-value"><?= htmlspecialchars($service['evaluation_status']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Schedule Card -->
                <div class="col-md-6">
                    <div class="info-card schedule-card">
                        <div class="card-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h6 class="card-title">Schedule</h6>
                        <div class="card-content">
                            <div class="info-row">
                                <div class="info-label">Date</div>
                                <div class="info-value fw-medium">
                                    <?= date('F j, Y', strtotime($service['scheduled_date'])) ?>
                                </div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Time</div>
                                <div class="info-value">
                                    <?= date('g:i A', strtotime($service['scheduled_time'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Client Card -->
                <div class="col-12">
                    <div class="info-card client-card">
                        <div class="card-icon">
                            <i class="bi bi-person"></i>
                        </div>
                        <h6 class="card-title">Client Details</h6>
                        <div class="card-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">Name</div>
                                        <div class="info-value fw-medium">
                                            <?= htmlspecialchars($service['client_name']) ?>
                                            <span class="client-type"><?= htmlspecialchars($service['client_type']) ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($service['company_name'])): ?>
                                        <div class="info-row">
                                            <div class="info-label">Company</div>
                                            <div class="info-value"><?= htmlspecialchars($service['company_name']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">Contact</div>
                                        <div class="info-value"><?= htmlspecialchars($service['client_contact']) ?></div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="info-label">Address</div>
                                        <div class="info-value"><?= htmlspecialchars($service['client_address']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0 px-4 pb-4">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
            
        </div>
    </div>

    <style>
    @import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
    
    .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }
    
    .header-accent {
        height: 6px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
    }
    
    .modal-title {
        color: #1f2937;
        letter-spacing: -0.025em;
    }
    
    .status-banner {
        padding: 16px;
        background-color: #f8fafc;
        border-radius: 12px;
    }
    
    .status-indicator {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    
    .status-indicator.confirmed {
        background-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
    }
    
    .status-indicator.pending {
        background-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
    }
    
    .status-indicator.cancelled {
        background-color: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.2);
    }
    
    .status-indicator.completed {
        background-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }
    
    .info-card {
        background-color: #f8fafc;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        position: relative;
        transition: all 0.2s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .card-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        margin-bottom: 12px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-size: 18px;
    }
    
    .service-card .card-icon {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }
    
    .schedule-card .card-icon {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .client-card .card-icon {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }
    
    .card-title {
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 16px;
        font-size: 1rem;
    }
    
    .card-content {
        color: #4b5563;
    }
    
    .info-row {
        margin-bottom: 14px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 4px;
        font-weight: 500;
    }
    
    .info-value {
        color: #1f2937;
    }
    
    .type-pill {
        display: inline-block;
        padding: 4px 10px;
        background-color: #e2e8f0;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        color: #4b5563;
    }
    
    .client-type {
        display: inline-block;
        font-size: 0.7rem;
        padding: 2px 8px;
        background-color: #e2e8f0;
        border-radius: 20px;
        color: #4b5563;
        margin-left: 8px;
        vertical-align: middle;
    }
    
    .btn-primary {
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        border: none;
        border-radius: 8px;
        font-weight: 500;
        padding: 8px 16px;
    }
    
    .btn-primary:hover {
        background: linear-gradient(90deg, #4f46e5, #7c3aed);
    }
    
    .btn-light {
        background-color: #f3f4f6;
        border: none;
        border-radius: 8px;
        color: #4b5563;
        font-weight: 500;
        padding: 8px 16px;
    }
    
    .btn-light:hover {
        background-color: #e5e7eb;
    }
    
    @media (max-width: 768px) {
        .info-card {
            margin-bottom: 16px;
        }
    }
    </style>
    <?php
}
?>