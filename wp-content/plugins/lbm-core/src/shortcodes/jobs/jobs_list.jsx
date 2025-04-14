import React, { useEffect, useState } from 'react';
import LBMCard from "../../common/controls/LBMCard.jsx";
import {fetchData} from "../../services/fetchData.js";

const ENGINEER_ID = lbm_settings.engineer_id;

const JobsList = () => {
    const [jobData, setJobData] = useState([]);

    useEffect(() => {
        fetchData('frohub/get_past_jobs', (response) => {
            if (response.success && response.data?.pastJobs) {
                const grouped = groupJobsByDate(response.data.pastJobs);
                setJobData(grouped);
            } else {
                console.error('Failed to fetch jobs:', response);
            }
        }, {
            engineerId: ENGINEER_ID
        });
    }, []);

    const groupJobsByDate = (jobs) => {
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
                id: job.Job_Id?.replace("JOB - ", ""),
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
    };

    return (
        <div className="p-4 md:p-6">
            <h3 className="text-lg font-semibold mb-4">My Past Jobs</h3>
            {jobData.length === 0 ? (
                <p>Loading jobs...</p>
            ) : (
                jobData.map(({ date, jobs }) => (
                    <div key={date} className="mb-8">
                        <h4 className="text-base font-medium mb-2">{date}</h4>
                        <div className="flex flex-col gap-4">
                            {jobs.map((job, index) => (
                                <a
                                    key={index}
                                    href={`/view-job/?id=${job.id}`}
                                    className="block"
                                >
                                    <LBMCard {...job} />
                                </a>
                            ))}
                        </div>
                    </div>
                ))
            )}
        </div>
    );
};

export default JobsList;
