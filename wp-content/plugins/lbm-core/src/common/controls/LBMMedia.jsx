import React, { useState } from 'react';

const LBMMedia = () => {
    const [images, setImages] = useState([]);

    const handleUpload = (e) => {
        const files = Array.from(e.target.files);
        const previews = files.map(file => URL.createObjectURL(file));
        setImages(prev => [...prev, ...previews]);
    };

    const handleRemove = (index) => {
        setImages(prev => prev.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-3">
            <label className="block bg-black !text-white text-sm text-center py-2 rounded cursor-pointer">
                Upload
                <input type="file" className="hidden" multiple accept="image/*" onChange={handleUpload} />
            </label>
            <div className="grid grid-cols-2 gap-2">
                {images.map((img, idx) => (
                    <div key={idx} className="relative bg-gray-100 aspect-square">
                        <button
                            onClick={() => handleRemove(idx)}
                            className="absolute top-1 right-1 text-xs bg-black !text-white rounded-full w-5 h-5 flex items-center justify-center"
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
