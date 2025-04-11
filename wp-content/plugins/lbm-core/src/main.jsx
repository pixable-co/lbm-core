import ViewSingleJob from './shortcodes/JOBS/view_single_job';
import UpcomingJobsList from './shortcodes/JOBS/upcoming_jobs_list';
import JobsList from './shortcodes/JOBS/jobs_list';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import './index.css';

const jobsListElements = document.querySelectorAll('.jobs_list');
jobsListElements.forEach(element => {
    const key = element.getAttribute('data-key');
    createRoot(element).render(
        <JobsList dataKey={key} />
    );
});

const upcomingJobsListElements = document.querySelectorAll('.upcoming_jobs_list');
upcomingJobsListElements.forEach(element => {
    const key = element.getAttribute('data-key');
    createRoot(element).render(
        <UpcomingJobsList dataKey={key} />
    );
});


const viewSingleJobElements = document.querySelectorAll('.view_single_job');
viewSingleJobElements.forEach(element => {
    const key = element.getAttribute('data-key');
    createRoot(element).render(
        <ViewSingleJob dataKey={key} />
    );
});