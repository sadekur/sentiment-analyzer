import React, { useState, useEffect } from "react";

const Overview = () => {
    const [activeTab, setActiveTab] = useState("all");
    const [counts, setCounts] = useState({
        all: 0,
        positive: 0,
        neutral: 0,
        negative: 0,
    });
    const [posts, setPosts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [total, setTotal] = useState(0);
    const perPage = 10;

    // Fetch posts based on active tab
    const fetchPosts = async (sentiment, page = 1) => {
        setLoading(true);
        try {
            // Build URL with parameters
            let url = `${SENTIMENT_ANALYZER?.apiUrl}/posts?page=${page}&per_page=${perPage}`;
            
            // Add sentiment filter only if not "all"
            if (sentiment !== 'all') {
                url += `&sentiment=${sentiment}`;
            }

            const response = await fetch(url, {
                headers: {
                    "X-WP-Nonce": SENTIMENT_ANALYZER?.nonce,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                setPosts(data.posts || []);
                setTotal(data.total || 0);
                setTotalPages(data.total_pages || 1);
                
                // Update counts from the response
                if (data.sentiment_counts) {
                    setCounts(data.sentiment_counts);
                }
            }
        } catch (error) {
            console.error('Error fetching posts:', error);
            setPosts([]);
        } finally {
            setLoading(false);
        }
    };

    // Initial load
    useEffect(() => {
        fetchPosts(activeTab, currentPage);
    }, [activeTab, currentPage]);

    const handleTabChange = (tab) => {
        setActiveTab(tab);
        setCurrentPage(1);
    };

    const getSentimentBadgeClass = (sentiment) => {
        const classes = {
            positive: 'bg-green-100 text-green-800',
            neutral: 'bg-yellow-100 text-yellow-800',
            negative: 'bg-red-100 text-red-800',
        };
        return classes[sentiment] || 'bg-gray-100 text-gray-800';
    };

    const tabs = [
        { key: 'all', label: 'All', count: counts.all },
        { key: 'positive', label: 'Positive', count: counts.positive },
        { key: 'neutral', label: 'Neutral', count: counts.neutral },
        { key: 'negative', label: 'Negative', count: counts.negative },
    ];

    return (
       <div>Hello from Overview</div>
    );
};

export default Overview;