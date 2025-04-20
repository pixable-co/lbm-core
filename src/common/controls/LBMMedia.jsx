import React, { useState } from 'react';

const LBMMedia = () => {
    const [images, setImages] = useState([]);

    const handleUpload = async (e) => {
        const files = Array.from(e.target.files);
        const previews = files.map(file => URL.createObjectURL(file));
        setImages(prev => [...prev, ...previews]);

        try {
            const checkRes = await fetch(lbm_settings.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'lbm/zoho_check_connection',
                    _ajax_nonce: lbm_settings.nonce,
                }),
            });

            const result = await checkRes.json();
            if (!result.success) {
                const clientId = '1000.WHI5I22WABVPMWMO7RPJ6AYNVSK42T';
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

            for (const file of files) {
                const formData = new FormData();
                formData.append('action', 'lbm/zoho_workdrive_upload');
                formData.append('_ajax_nonce', lbm_settings.nonce);
                formData.append('file', file);

                const uploadRes = await fetch(lbm_settings.ajax_url, {
                    method: 'POST',
                    body: formData,
                });

                const data = await uploadRes.json();
                console.log('📦 Upload result:', data);

                if (!data.success) {
                    alert(`Upload failed: ${data.message}`);
                }
            }
        } catch (err) {
            console.error('❌ Upload error:', err);
        }
    };

    const handleRemove = (index) => {
        setImages(prev => prev.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <label className="block bg-black text-white text-sm text-center py-2 rounded cursor-pointer">
                Upload
                <input type="file" className="hidden" multiple accept="image/*" onChange={handleUpload} />
            </label>
            <div className="grid grid-cols-2 gap-2">
                {images.map((img, idx) => (
                    <div key={idx} className="relative bg-gray-100 aspect-square">
                        <button
                            onClick={() => handleRemove(idx)}
                            className="absolute top-1 right-1 text-xs bg-black text-white rounded-full w-5 h-5 flex items-center justify-center"
                        >
                            ×
                        </button>
                        <img src={img} alt="upload" className="object-cover w-full h-full rounded" />
                    </div>
                ))}
            </div>
        </div>
    );
};

export default LBMMedia;
