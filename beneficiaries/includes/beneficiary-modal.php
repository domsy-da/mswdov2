
<div class="modal-overlay" id="beneficiaryModal">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Beneficiary</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form class="beneficiary-form" id="beneficiaryForm" method="POST">
                <div class="form-grid">
                    <!-- Personal Information -->
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="birthday" class="form-label">Birthday:</label>
                        <input type="date" id="birthday" name="birthday" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="age" class="form-label">Age:</label>
                        <input type="number" id="age" name="age" class="form-input" min="0" max="120" required>
                    </div>

                    <div class="form-group">
                        <label for="gender" class="form-label">Gender/Sex:</label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="">-- Select Gender --</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-group">
                        <label for="civilStatus" class="form-label">Civil Status:</label>
                        <select id="civilStatus" name="civilStatus" class="form-select" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Separated">Separated</option>
                            <option value="Divorced">Divorced</option>
                        </select>
                    </div>

                    <!-- Location Information -->
                    <div class="form-group">
                        <label for="barangay" class="form-label">Barangay:</label>
                        <select id="barangay" name="barangay" class="form-select" required onchange="loadSitios(this.value)">
                            <option value="">Select a Barangay</option>
                            <?php foreach ($barangays as $barangay): ?>
                                <option value="<?= htmlspecialchars($barangay['name']) ?>">
                                    <?= htmlspecialchars($barangay['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sitio" class="form-label">Sitio/Purok:</label>
                        <select id="sitio" name="sitio" class="form-select" required>
                            <option value="">Select Sitio/Purok</option>
                        </select>
                    </div>

                    <!-- Other Fields -->
                    <div class="form-group">
                        <label for="birthplace" class="form-label">Birthplace:</label>
                        <input type="text" id="birthplace" name="birthplace" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="education" class="form-label">Educational Attainment:</label>
                        <input type="text" id="education" name="education" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="occupation" class="form-label">Occupation:</label>
                        <input type="text" id="occupation" name="occupation" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="religion" class="form-label">Religion:</label>
                        <input type="text" id="religion" name="religion" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="dateAdded" class="form-label">Date Added:</label>
                        <input type="date" id="dateAdded" name="dateAdded" class="form-input" readonly>
                    </div>
                </div>
                
                <!-- Hidden Fields -->
                <input type="hidden" id="beneficiaryId" name="beneficiaryId" value="">
                <input type="hidden" id="action" name="action" value="add">
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="submit" form="beneficiaryForm" class="btn btn-primary">Save Beneficiary</button>
        </div>
    </div>
</div>