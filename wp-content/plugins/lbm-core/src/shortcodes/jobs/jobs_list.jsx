import React from 'react';
import LBMCard from "../../common/controls/LBMCard.jsx";

const jobData = [
    {
        date: '10 Apr 2025',
        jobs: [
            {
                time: '10:00',
                duration: '2 hours 30 mins',
                address: ['27 Albert Gardens', 'London', 'E1 0LH'],
                status: 'Created',
                id: 11786,
            },
            {
                time: '12:00',
                duration: '2 hours 00 mins',
                address: ['27 Albert Gardens', 'London', 'E1 0LH'],
                status: 'Created',
                id: 11798,
            },
        ]
    },
    {
        date: '21 Apr 2025',
        jobs: [
            {
                time: '10:00',
                duration: '2 hours 30 mins',
                address: ['27 Albert Gardens', 'London', 'E1 0LH'],
                status: 'Created',
                id: 11886,
            },
            {
                time: '12:00',
                duration: '2 hours 00 mins',
                address: ['27 Albert Gardens', 'London', 'E1 0LH'],
                status: 'Created',
                id: 11886,
            },
        ]
    }
];

const JobsList = () => {
    return (
        <div className="p-4 md:p-6">
            <h3 className="text-lg font-semibold mb-4">My Upcoming Jobs</h3>
            {jobData.map(({ date, jobs }) => (
                <div key={date} className="mb-8">
                    <h4 className="text-base font-medium mb-2">{date}</h4>
                    <div className="flex flex-col gap-4">
                        {jobs.map((job, index) => (
                            <LBMCard key={index} {...job} />
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
};

export default JobsList;
