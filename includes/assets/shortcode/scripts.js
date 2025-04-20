document.addEventListener("DOMContentLoaded", function () {
    const containers = document.querySelectorAll(".jobs_list");

    containers.forEach(container => {
        const engineerId = lbm_settings.engineer_id;

        if (!engineerId) {
            container.innerHTML = "<p>Engineer ID is missing.</p>";
            return;
        }

        fetch(lbm_settings.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'frohub/get_past_jobs',
                engineerId: engineerId,
                _ajax_nonce: lbm_settings.nonce
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && response.data?.pastJobs) {
                    const groupedJobs = groupJobsByDate(response.data.pastJobs);
                    renderJobs(container, groupedJobs);
                } else {
                    container.innerHTML = "<p>Failed to load jobs.</p>";
                    console.error("Job fetch failed:", response);
                }
            })
            .catch(error => {
                container.innerHTML = "<p>Failed to load jobs (network error).</p>";
                console.error("Fetch error:", error);
            });
    });

    function groupJobsByDate(jobs) {
        const map = {};

        jobs.forEach(job => {
            const date = new Date(job.New_Work_Date_Time).toLocaleDateString("en-GB", {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            const start = new Date(job.New_Work_Date_Time);
            const end = new Date(job.New_Work_End_Date_Time);
            const durationMs = end - start;
            const durationHours = Math.floor(durationMs / (1000 * 60 * 60));
            const durationMins = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));

            const formattedJob = {
                id: job.CRM_Job_Id,
                displayId: job.Job_Number?.replace("JOB - ", ""),
                time: start.toLocaleTimeString("en-GB", { hour: '2-digit', minute: '2-digit' }),
                duration: `${durationHours} hour${durationHours > 1 ? 's' : ''} ${durationMins} mins`,
                address: [
                    job.Tenant_Address_1?.name,
                    job.Tenant_City,
                    job.Tenant_Post_Code
                ],
                status: job.Status
            };

            if (!map[date]) {
                map[date] = [];
            }

            map[date].push(formattedJob);
        });

        return Object.entries(map).map(([date, jobs]) => ({ date, jobs }));
    }

    function renderJobs(container, data) {
        const wrapper = document.createElement("div");
        wrapper.className = "jobs-wrapper";

        const heading = document.createElement("h3");
        heading.className = "jobs-heading";
        heading.textContent = "My Past Jobs";
        wrapper.appendChild(heading);

        if (data.length === 0) {
            wrapper.innerHTML += "<p>No past jobs found.</p>";
        } else {
            data.forEach(({ date, jobs }) => {
                const section = document.createElement("div");
                section.className = "jobs-section";

                const dateHeading = document.createElement("h4");
                dateHeading.className = "jobs-date-heading";
                dateHeading.textContent = date;
                section.appendChild(dateHeading);

                const jobList = document.createElement("div");
                jobList.className = "jobs-list";

                jobs.forEach(job => {
                    const link = document.createElement("a");
                    link.href = `/view-job/?id=${job.id}`;
                    link.className = "job-link";

                    const card = document.createElement("div");
                    card.className = "job-card";

                    const topRow = document.createElement("div");
                    topRow.className = "job-card-header";

                    const timeBlock = document.createElement("div");
                    const timeEl = document.createElement("div");
                    timeEl.className = "job-time";
                    timeEl.textContent = job.time;

                    const durationEl = document.createElement("div");
                    durationEl.className = "job-duration";
                    durationEl.textContent = job.duration;

                    timeBlock.appendChild(timeEl);
                    timeBlock.appendChild(durationEl);

                    const idEl = document.createElement("div");
                    idEl.className = "job-id";
                    idEl.textContent = job.displayId;

                    topRow.appendChild(timeBlock);
                    topRow.appendChild(idEl);

                    const addressEl = document.createElement("div");
                    addressEl.className = "job-address";
                    job.address.forEach(line => {
                        const lineDiv = document.createElement("div");
                        lineDiv.textContent = line;
                        addressEl.appendChild(lineDiv);
                    });

                    const statusEl = document.createElement("div");
                    statusEl.innerHTML = `<span class="job-status">${job.status}</span>`;

                    card.appendChild(topRow);
                    card.appendChild(addressEl);
                    card.appendChild(statusEl);

                    link.appendChild(card);
                    jobList.appendChild(link);
                });

                section.appendChild(jobList);
                wrapper.appendChild(section);
            });
        }

        container.appendChild(wrapper);
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const containers = document.querySelectorAll(".upcoming_jobs_list");

    containers.forEach(container => {
        const engineerId = lbm_settings.engineer_id;

        if (!engineerId) {
            container.innerHTML = "<p>Engineer ID is missing.</p>";
            return;
        }

        fetch(lbm_settings.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'frohub/get_upcoming_jobs',
                engineerId: engineerId,
                _ajax_nonce: lbm_settings.nonce
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response.success && response.data?.upcomingJobs) {
                    const groupedJobs = groupJobsByDate(response.data.upcomingJobs);
                    renderJobs(container, groupedJobs);
                } else {
                    container.innerHTML = "<p>Failed to load upcoming jobs.</p>";
                    console.error("Job fetch failed:", response);
                }
            })
            .catch(error => {
                container.innerHTML = "<p>Failed to load jobs (network error).</p>";
                console.error("Fetch error:", error);
            });
    });

    function groupJobsByDate(jobs) {
        const map = {};

        jobs.forEach(job => {
            const date = new Date(job.New_Work_Date_Time).toLocaleDateString("en-GB", {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });

            const start = new Date(job.New_Work_Date_Time);
            const end = new Date(job.New_Work_End_Date_Time);
            const durationMs = end - start;
            const durationHours = Math.floor(durationMs / (1000 * 60 * 60));
            const durationMins = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));

            const formattedJob = {
                id: job.CRM_Job_Id,
                displayId: job.Job_Number?.replace("JOB - ", ""),
                time: start.toLocaleTimeString("en-GB", { hour: '2-digit', minute: '2-digit' }),
                duration: `${durationHours} hour${durationHours > 1 ? 's' : ''} ${durationMins} mins`,
                address: [
                    job.Tenant_Address_1?.name,
                    job.Tenant_City,
                    job.Tenant_Post_Code
                ],
                status: job.Status
            };

            if (!map[date]) {
                map[date] = [];
            }

            map[date].push(formattedJob);
        });

        return Object.entries(map).map(([date, jobs]) => ({ date, jobs }));
    }

    function renderJobs(container, data) {
        const wrapper = document.createElement("div");
        wrapper.className = "jobs-wrapper";

        const heading = document.createElement("h3");
        heading.className = "jobs-heading";
        heading.textContent = "Upcoming Jobs";
        wrapper.appendChild(heading);

        if (data.length === 0) {
            wrapper.innerHTML += "<p>No upcoming jobs found.</p>";
        } else {
            data.forEach(({ date, jobs }) => {
                const section = document.createElement("div");
                section.className = "jobs-section";

                const dateHeading = document.createElement("h4");
                dateHeading.className = "jobs-date-heading";
                dateHeading.textContent = date;
                section.appendChild(dateHeading);

                const jobList = document.createElement("div");
                jobList.className = "jobs-list";

                jobs.forEach(job => {
                    const link = document.createElement("a");
                    link.href = `/view-job/?id=${job.id}`;
                    link.className = "job-link";

                    const card = document.createElement("div");
                    card.className = "job-card";

                    const topRow = document.createElement("div");
                    topRow.className = "job-card-header";

                    const timeBlock = document.createElement("div");
                    const timeEl = document.createElement("div");
                    timeEl.className = "job-time";
                    timeEl.textContent = job.time;

                    const durationEl = document.createElement("div");
                    durationEl.className = "job-duration";
                    durationEl.textContent = job.duration;

                    timeBlock.appendChild(timeEl);
                    timeBlock.appendChild(durationEl);

                    const idEl = document.createElement("div");
                    idEl.className = "job-id";
                    idEl.textContent = job.displayId;

                    topRow.appendChild(timeBlock);
                    topRow.appendChild(idEl);

                    const addressEl = document.createElement("div");
                    addressEl.className = "job-address";
                    job.address.forEach(line => {
                        const lineDiv = document.createElement("div");
                        lineDiv.textContent = line;
                        addressEl.appendChild(lineDiv);
                    });

                    const statusEl = document.createElement("div");
                    statusEl.innerHTML = `<span class="job-status">${job.status}</span>`;

                    card.appendChild(topRow);
                    card.appendChild(addressEl);
                    card.appendChild(statusEl);

                    link.appendChild(card);
                    jobList.appendChild(link);
                });

                section.appendChild(jobList);
                wrapper.appendChild(section);
            });
        }

        container.appendChild(wrapper);
    }
});


// view-single-job.js

// view-single-job.js

// Utility: Upload file to Zoho WorkDrive
async function uploadToZoho(file, previewContainer) {
    const checkRes = await fetch(lbm_settings.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'lbm/zoho_check_connection',
            _ajax_nonce: lbm_settings.nonce,
        }),
    });

    const result = await checkRes.json();
    if (!result.success) {
        alert('Not authorized with Zoho. Redirecting...');
        const clientId = '1000.ZDSET1BTZH3EOM50JCC6I58FH1GLSZ';
        const redirectUri = 'http://localhost:10028';
        const scope = [
            'workdrive.files.READ',
            'workdrive.files.CREATE',
            'workdrive.files.UPDATE',
            'workdrive.workspace.READ',
            'workdrive.team.READ',
        ].join(',');

        const authUrl = `https://accounts.zoho.eu/oauth/v2/auth?scope=${scope}&client_id=${clientId}&response_type=code&access_type=offline&prompt=consent&redirect_uri=${encodeURIComponent(redirectUri)}`;
        window.location.href = authUrl;
        return;
    }

    const formData = new FormData();
    formData.append('action', 'lbm/zoho_workdrive_upload');
    formData.append('_ajax_nonce', lbm_settings.nonce);
    formData.append('file', file);

    const uploadRes = await fetch(lbm_settings.ajax_url, {
        method: 'POST',
        body: formData,
    });

    const data = await uploadRes.json();
    console.log('Upload result:', data);

    if (!data.success) {
        alert(`Upload failed: ${data.message}`);
        return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'media-thumb-wrapper';

    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.className = 'media-thumb';

    const removeBtn = document.createElement('button');
    removeBtn.textContent = '×';
    removeBtn.className = 'remove-thumb';
    removeBtn.onclick = () => wrapper.remove();

    wrapper.appendChild(img);
    wrapper.appendChild(removeBtn);
    previewContainer.appendChild(wrapper);
}

function initSignaturePad(container) {
    const canvas = document.createElement('canvas');
    canvas.className = 'signature-canvas';
    container.appendChild(canvas);

    // Set width after DOM is ready
    requestAnimationFrame(() => {
        canvas.width = container.offsetWidth || 400;
        canvas.height = 100;

        const ctx = canvas.getContext('2d');
        let drawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            } else {
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            }
        }

        function startDraw(e) {
            e.preventDefault();
            const { x, y } = getPos(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
            drawing = true;
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const { x, y } = getPos(e);
            ctx.lineTo(x, y);
            ctx.strokeStyle = '#000';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.stroke();
        }

        function stopDraw() {
            drawing = false;
            ctx.closePath();
        }

        // Mouse Events
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        // Touch Events (Mobile)
        canvas.addEventListener('touchstart', startDraw);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDraw);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector(".view_single_job");
    if (!container) return;

    const params = new URLSearchParams(window.location.search);
    const jobId = params.get("id");
    if (!jobId) return (container.innerHTML = "<p>Missing job ID.</p>");

    container.innerHTML = `<p class="job-loading">Loading job details...</p>`;

    fetch(lbm_settings.ajax_url, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            action: "frohub/get_job_by_id",
            jobId: jobId,
            _ajax_nonce: lbm_settings.nonce,
        }),
    })
        .then((res) => res.json())
        .then((response) => {
            if (response.success && response.data?.jobDetails) {
                renderJob(container, response.data.jobDetails);
            } else {
                container.innerHTML = `<p>Failed to load job: ${response?.data?.message || "unknown error"}</p>`;
            }
        })
        .catch((err) => {
            container.innerHTML = `<p>Error fetching job details.</p>`;
            console.error(err);
        });

    function renderJob(container, job) {
        const start = new Date(job.New_Work_Date_Time);
        const end = new Date(job.New_Work_End_Date_Time);
        const dateStr = start.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
        const timeStr = start.toLocaleTimeString("en-GB", {
            hour: "2-digit",
            minute: "2-digit",
        });
        const durationHours = Math.floor((end - start) / (1000 * 60 * 60));
        const durationMins = Math.floor(((end - start) % (1000 * 60 * 60)) / (1000 * 60));
        const duration = `${durationHours} hour${durationHours !== 1 ? "s" : ""} ${durationMins} mins`;

        container.innerHTML = `
      <div class="job-wrapper">
        <div class="job-header">
          <h2>Job > ${job.Job_Id?.replace("JOB - ", "")}</h2>
          <span class="job-status">${job.Status}</span>
        </div>

        <div class="job-meta">
          <p><strong>Date:</strong> ${dateStr}</p>
          <p><strong>Time:</strong> ${timeStr}</p>
          <p><strong>Duration:</strong> ${duration}</p>
          <div>
            <strong>Address:</strong>
            <div>${job.Tenant_Address_1?.name || ""}</div>
            <div>${job.Tenant_City || ""}</div>
            <div>${job.Tenant_Post_Code || ""}</div>
          </div>
          ${job.Sales_Order ? `<p><strong>Sales Order:</strong> ${job.Sales_Order}</p>` : ""}
          <p><strong>Tenant:</strong> ${job.Tenant_Name} (${job.Tenant_Phone})
            <a href="tel:${job.Tenant_Phone}" class="job-call">📞</a>
          </p>
        </div>

        <div class="job-actions">
          <button class="btn btn-checkin" onclick="document.querySelector('.checkin-modal').style.display = 'flex'">Check-in</button>
          <button class="btn">Tenant not in</button>
          <button class="btn">Further Works</button>
        </div>

        <div class="job-media">
          <h4>Pre-work Images</h4>
          <div class="media-upload">
            <label class="media-upload-btn">
              Upload
              <input type="file" class="media-input" data-type="pre" multiple accept="image/*">
            </label>
            <div class="media-preview pre"></div>
          </div>
        </div>

        <div class="job-media">
          <h4>Post-work Images</h4>
          <div class="media-upload">
            <label class="media-upload-btn">
              Upload
              <input type="file" class="media-input" data-type="post" multiple accept="image/*">
            </label>
            <div class="media-preview post"></div>
          </div>
        </div>

        <div class="job-signature">
          <h4>Tenant Signature</h4>
          <div class="signature-pad"></div>
          <p class="job-sig-name">${job.Tenant_Name}<br>${dateStr}</p>
          <button class="btn w-full">Save</button>
        </div>

        <div class="job-notes">
          <textarea placeholder="Add a note..."></textarea>
          <button class="btn w-full">Add Note</button>

          <div class="note-entry">
            <p>Example note one.</p>
            <span>10:12 | ${dateStr}</span>
          </div>

          <div class="note-entry">
            <p>Example note two.</p>
            <span>12:41 | ${dateStr}</span>
          </div>
        </div>
      </div>

      <div class="checkin-modal" style="display:none;">
        <div class="modal-content">
          <button class="modal-close" onclick="this.closest('.checkin-modal').style.display = 'none'">&times;</button>
          <h2>Check-in</h2>
          <input type="datetime-local" value="${new Date().toISOString().slice(0, 16)}" class="checkin-time" />
          <button class="btn w-full mt-4" onclick="alert('Check-in saved!'); this.closest('.checkin-modal').style.display = 'none'">Confirm</button>
        </div>
      </div>
    `;

        // Signature pad
        const sigPad = container.querySelector(".signature-pad");
        if (sigPad) initSignaturePad(sigPad);

        // Uploaders
        const uploadInputs = container.querySelectorAll(".media-input");
        uploadInputs.forEach(input => {
            const preview = input.closest(".media-upload").querySelector(".media-preview");
            input.addEventListener("change", (e) => {
                Array.from(e.target.files).forEach(file => uploadToZoho(file, preview));
            });
        });
    }
});
