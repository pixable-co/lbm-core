import React from 'react';

const LBMCard = ({ time, duration, address, status, id }) => {
    return (
        <div className="border rounded-lg p-4 shadow-sm">
            <div className="flex justify-between items-start mb-2">
                <div>
                    <div className="text-sm font-medium">{time}</div>
                    <div className="text-xs text-gray-600">{duration}</div>
                </div>
                <div className="text-sm font-semibold">{id}</div>
            </div>
            <div className="text-sm text-gray-800 mb-3 leading-tight">
                {address.map((line, i) => (
                    <div key={i}>{line}</div>
                ))}
            </div>
            <div>
                <span className="bg-gray-200 text-gray-800 text-xs px-3 py-1 rounded-md">
                    {status}
                </span>
            </div>
        </div>
    );
};

export default LBMCard;
