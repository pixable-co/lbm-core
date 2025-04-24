import React, { useEffect, useState } from 'react';
import LBMMedia from "../../common/controls/LBMMedia.jsx";
import LBMCheckin from "../../common/controls/LBMCheckin.jsx";
import {fetchData} from "../../services/fetchData.js";

const ViewJob = () => {
    const [jobId, setJobId] = useState(null);
    const [job, setJob] = useState(null);
    const [showCheckin, setShowCheckin] = useState(false);

    useEffect(() => {
        // Get jobId from query string
        const params = new URLSearchParams(window.location.search);
        const idFromUrl = params.get('id');
        if (idFromUrl) setJobId(idFromUrl);
    }, []);

    useEffect(() => {
        if (jobId) {
            fetchData('frohub/get_job_by_id', (response) => {
                if (response.success && response.data?.jobDetails) {
                    setJob(response.data.jobDetails);
                } else {
                    console.error("Failed to load job details:", response.data?.message);
                }
            }, { jobId });
        }
    }, [jobId]);

    if (!job) return <p className="p-4">Loading job details...</p>;

    const {
        Job_Id,
        Status,
        New_Work_Date_Time,
        New_Work_End_Date_Time,
        Tenant_Name,
        Tenant_Phone,
        Tenant_Email,
        Tenant_Address_1,
        Tenant_Address_2,
        Tenant_City,
        Tenant_Post_Code,
        Sales_Order
    } = job;

    const start = new Date(New_Work_Date_Time);
    const end = new Date(New_Work_End_Date_Time);
    const dateStr = start.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const timeStr = start.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    const durationHours = Math.floor((end - start) / (1000 * 60 * 60));
    const durationMins = Math.floor(((end - start) % (1000 * 60 * 60)) / (1000 * 60));
    const duration = `${durationHours} hour${durationHours !== 1 ? 's' : ''} ${durationMins} mins`;

    return (
        <div className="p-4 md:p-6 max-w-md mx-auto space-y-6">
            <div className="flex justify-between items-center">
                <h2 className="font-semibold text-sm">Job &gt; {Job_Id?.replace("JOB - ", "")}</h2>
                <span className="text-xs bg-gray-200 px-2 py-1 rounded">{Status}</span>
            </div>

            <div className="text-sm space-y-2">
                <p><strong>Date:</strong> {dateStr}</p>
                <p><strong>Time:</strong> {timeStr}</p>
                <p><strong>Duration:</strong> {duration}</p>
                <div>
                    <strong>Address:</strong>
                    <div>{Tenant_Address_1?.name}</div>
                    <div>{Tenant_City}</div>
                    <div>{Tenant_Post_Code}</div>
                </div>
                {Sales_Order && <p><strong>Sales Order:</strong> {Sales_Order}</p>}
                <div className="flex items-center gap-2">
                    <p><strong>Tenant:</strong> {Tenant_Name} ({Tenant_Phone})</p>
                    <a href={`tel:${Tenant_Phone}`} className="text-xl">📞</a>
                </div>
            </div>

            <div className="flex flex-wrap gap-2">
                <button
                    className="bg-black text-white text-sm px-4 py-2 rounded"
                    onClick={() => setShowCheckin(true)}
                >
                    Check-in
                </button>
                <LBMCheckin
                    visible={showCheckin}
                    onClose={() => setShowCheckin(false)}
                    timestamp={new Date().toLocaleString('en-GB')}
                />
                <button className="bg-black text-white text-sm px-4 py-2 rounded">Tenant not in</button>
                <button className="bg-black text-white text-sm px-4 py-2 rounded">Further Works</button>
            </div>

            <div className="border rounded p-4">
                <h4 className="font-medium mb-2 text-sm">Pre-work Images</h4>
                <LBMMedia />
            </div>

            <div className="border rounded p-4">
                <h4 className="font-medium mb-2 text-sm">Post-work Images</h4>
                <LBMMedia />
            </div>

            <div className="border rounded p-4 space-y-2">
                <h4 className="font-medium text-sm">Tenant Signature</h4>
                <div className="border h-24 bg-white"></div>
                <p className="text-xs text-gray-500">{Tenant_Name}<br />{dateStr}</p>
                <button className="bg-black text-white text-sm w-full py-2 rounded">Save</button>
            </div>

            <div className="space-y-2">
                <textarea
                    rows={3}
                    className="w-full border rounded p-2 text-sm"
                    placeholder="Add a note..."
                />
                <button className="bg-black text-white text-sm w-full py-2 rounded">Add Note</button>

                <div className="text-xs text-gray-600">
                    <p className="mt-3">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                    <p className="text-gray-400 text-[11px]">10:12 | {dateStr}</p>

                    <p className="mt-3">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                    <p className="text-gray-400 text-[11px]">12:41 | {dateStr}</p>
                </div>
            </div>
        </div>
    );
};

export default ViewJob;
