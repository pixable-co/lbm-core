import React, { useState, useEffect } from 'react';
import { Modal, DatePicker } from 'antd';
import dayjs from 'dayjs';

const LBMCheckin = ({ visible, onClose, timestamp }) => {
    const [checkinTime, setCheckinTime] = useState(dayjs(timestamp));

    useEffect(() => {
        if (timestamp) {
            setCheckinTime(dayjs(timestamp));
        }
    }, [timestamp]);

    const handleConfirm = () => {
        console.log('Confirmed:', checkinTime.format('YYYY-MM-DD HH:mm'));
        onClose();
    };

    return (
        <Modal
            open={visible}
            onCancel={onClose}
            footer={null}
            centered
            closable={false}
            className="rounded-lg"
            maskStyle={{ backdropFilter: 'blur(2px)' }}
        >
            <div className="relative">
                {/* Close Button */}
                <button
                    onClick={onClose}
                    className="absolute top-2 right-2 text-2xl text-gray-500 hover:text-black transition"
                >
                    &times;
                </button>

                {/* Title */}
                <h2 className="text-lg font-semibold mb-5">Check-in</h2>

                {/* AntD DatePicker with fix */}
                <DatePicker
                    showTime
                    value={checkinTime}
                    onChange={setCheckinTime}
                    format="DD MMM YYYY, HH:mm"
                    className="w-full !rounded-md !h-10"
                    style={{ width: '100%' }}
                    getPopupContainer={() => document.body} // ensures no clipping
                />

                {/* Confirm Button */}
                <button
                    className="mt-6 bg-black text-white w-full py-2 rounded text-sm hover:bg-gray-900 transition"
                    onClick={handleConfirm}
                >
                    Confirm
                </button>
            </div>
        </Modal>
    );
};

export default LBMCheckin;
