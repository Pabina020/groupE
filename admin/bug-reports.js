// Function to fetch and display bug reports
function loadBugReports() {
    fetch('bugs.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tableBody = document.getElementById('bugReportsTable');
            
            // Clear existing rows
            tableBody.innerHTML = '';
            
            data.forEach(bug => {
                const row = document.createElement('tr');
                
                // Determine badge class based on severity
                let severityClass = 'bg-secondary';
                if (bug.severity === 'medium') severityClass = 'bg-warning';
                else if (bug.severity === 'high') severityClass = 'bg-danger';
                else if (bug.severity === 'critical') severityClass = 'bg-dark';
                
                // Determine status badge class
                let statusClass = 'bg-secondary';
                if (bug.status === 'in_progress') statusClass = 'bg-primary';
                else if (bug.status === 'resolved') statusClass = 'bg-success';
                
                row.innerHTML = `
                    <td>${bug.id}</td>
                    <td>${bug.email}</td>
                    <td class="text-truncate" style="max-width: 200px;">${bug.description}</td>
                    <td><span class="badge ${severityClass}">${bug.severity}</span></td>
                    <td><span class="badge ${statusClass}">${bug.status}</span></td>
                    <td>${new Date(bug.created_at).toLocaleDateString()}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary view-bug" data-id="${bug.id}">View</button>
                    </td>
                `;
                
                tableBody.appendChild(row);
            });
            
            // Add event listeners to view buttons
            document.querySelectorAll('.view-bug').forEach(button => {
                button.addEventListener('click', function() {
                    const bugId = this.getAttribute('data-id');
                    viewBugReport(bugId);
                });
            });
        })
        .catch(error => {
            console.error('Error fetching bug reports:', error);
            const tableBody = document.getElementById('bugReportsTable');
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        Failed to load bug reports. Please try again later.
                    </td>
                </tr>
            `;
        });
}

// Function to view bug report details in modal
function viewBugReport(bugId) {
    fetch(`bug-details.php?id=${bugId}`)
        .then(response => response.json())
        .then(bug => {
            const modalBody = document.getElementById('bugReportDetails');
            let screenshotHtml = '';
            
            if (bug.screenshot_path) {
                screenshotHtml = `
                    <div class="mb-3">
                        <h6>Screenshot</h6>
                        <img src="../${bug.screenshot_path}" class="img-fluid rounded" alt="Bug screenshot">
                    </div>
                `;
            }
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> ${bug.email}</p>
                        <p><strong>Submitted:</strong> ${new Date(bug.created_at).toLocaleString()}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Severity:</strong> <span class="badge ${getSeverityClass(bug.severity)}">${bug.severity}</span></p>
                        <p><strong>Status:</strong> <span class="badge ${getStatusClass(bug.status)}">${bug.status.replace('_', ' ')}</span></p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6>Description</h6>
                    <p>${bug.description}</p>
                </div>
                
                <div class="mb-3">
                    <h6>Steps to Reproduce</h6>
                    <p>${bug.steps}</p>
                </div>
                
                ${screenshotHtml}
            `;
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('bugReportModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching bug details:', error);
            alert('Failed to load bug details');
        });
}

// Helper functions for badge classes
function getSeverityClass(severity) {
    switch(severity) {
        case 'medium': return 'bg-warning';
        case 'high': return 'bg-danger';
        case 'critical': return 'bg-dark';
        default: return 'bg-secondary';
    }
}

function getStatusClass(status) {
    switch(status) {
        case 'in_progress': return 'bg-primary';
        case 'resolved': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', loadBugReports);