import React, { useState } from 'react';
import LBMMedia from "../../common/controls/LBMMedia.jsx";
import LBMCheckin from "../../common/controls/LBMCheckin.jsx";
const ViewJob = () => {
    const [showCheckin, setShowCheckin] = useState(false);

// Simulated timestamp (you can replace with actual logic)
    const currentTimestamp = '10 Apr 2025, 09:55';

    return (
        <div className="p-4 md:p-6 max-w-md mx-auto space-y-6">
            {/* Header */}
            <div className="flex justify-between items-center">
                <h2 className="font-semibold text-sm">Job &gt; 11786</h2>
                <span className="text-xs bg-gray-200 px-2 py-1 rounded">Created</span>
            </div>

            {/* Job Details */}
            <div className="text-sm space-y-2">
                <p><strong>Date:</strong> 10 Apr 2025</p>
                <p><strong>Time:</strong> 10:00</p>
                <p><strong>Duration:</strong> 2 hours 30 mins</p>
                <div>
                    <strong>Address:</strong>
                    <div>27 Albert Gardens</div>
                    <div>London</div>
                    <div>E1 0LH</div>
                </div>
                <div>
                    <strong>Description:</strong>
                    <ul className="list-disc ml-5">
                        <li>Replace kitchen cabinet doors</li>
                        <li>Fix tap</li>
                    </ul>
                </div>
                <p><strong>Housing Association:</strong> Spitalfields</p>
                <div className="flex items-center gap-2">
                    <p><strong>Tenant:</strong> Aniek Abbot (07452 122 234)</p>
                    <button className="text-xl">📞</button>
                </div>
            </div>

            {/* Action Buttons */}
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
                    timestamp={currentTimestamp}
                />
                <button className="bg-black !text-white text-sm px-4 py-2 rounded">Tenant not in</button>
                <button className="bg-black !text-white text-sm px-4 py-2 rounded">Further Works</button>
            </div>

            {/* Pre-work Images */}
            <div className="border rounded p-4">
                <h4 className="font-medium mb-2 text-sm">Pre-work Images</h4>
                <LBMMedia />
            </div>

            {/* Post-work Images */}
            <div className="border rounded p-4">
                <h4 className="font-medium mb-2 text-sm">Post-work Images</h4>
                <LBMMedia />
            </div>

            {/* Tenant Signature */}
            <div className="border rounded p-4 space-y-2">
                <h4 className="font-medium text-sm">Tenant Signature</h4>
                <div className="border h-24 bg-white"></div>
                <p className="text-xs text-gray-500">Aniek Abbot<br />10 Apr 2025</p>
                <button className="bg-black !text-white text-sm w-full py-2 rounded">Save</button>
            </div>

            {/* Notes */}
            <div className="space-y-2">
                <textarea
                    rows={3}
                    className="w-full border rounded p-2 text-sm"
                    placeholder="Add a note..."
                />
                <button className="bg-black !text-white text-sm w-full py-2 rounded">Add Note</button>

                {/* Example past notes */}
                <div className="text-xs text-gray-600">
                    <p className="mt-3">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                    <p className="text-gray-400 text-[11px]">10:12 | 10 Apr 2025</p>

                    <p className="mt-3">Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
                    <p className="text-gray-400 text-[11px]">12:41 | 10 Apr 2025</p>
                </div>
            </div>
        </div>
    );
};

export default ViewJob;
