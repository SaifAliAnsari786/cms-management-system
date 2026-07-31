import { useEffect, useState } from "react";
import { getPages } from "../services/pageService";
import PageCard from "../components/PageCard";
import Loader from "../components/Loader";

export default function Home() {
    const [pages, setPages] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState("");

    useEffect(() => {
        loadPages();
    }, []);

    const loadPages = async () => {
        try {
            setLoading(true);

            const data = await getPages();
            setPages(data);
        } catch (err) {
            console.error(err);
            setError("Failed to load pages.");
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <Loader />;

    if (error) {
        return (
            <div className="max-w-7xl mx-auto py-20 text-center text-red-500">
                {error}
            </div>
        );
    }

    return (
        <>
            {/* Hero Section */}
            <section className="relative overflow-hidden pt-20 md:pt-28 pb-20 md:pb-28 px-6 bg-white">
                <div className="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-[600px] h-[600px] bg-indigo-100 rounded-full blur-3xl pointer-events-none"></div>

                <div className="max-w-[1280px] mx-auto text-center relative">
                    <h1 className="text-5xl md:text-7xl font-bold tracking-tight text-gray-900 mb-6">
                        Welcome to Our CMS
                    </h1>

                    <p className="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto">
                        A simple platform for managing and viewing dynamic
                        content.
                    </p>
                </div>
            </section>

            {/* Published Pages */}
            <section className="bg-gray-50 py-20 px-6">
                <div className="max-w-[1280px] mx-auto">

                    <div className="mb-12">
                        <h2 className="text-4xl font-bold text-gray-900 mb-2">
                            Published Pages
                        </h2>

                        <p className="text-gray-500">
                            Browse all published pages.
                        </p>
                    </div>

                    {pages.length === 0 ? (
                        <div className="text-center py-20 text-gray-500">
                            No pages found.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {pages.map((page) => (
                                <PageCard
                                    key={page.id}
                                    page={page}
                                />
                            ))}
                        </div>
                    )}

                </div>
            </section>
        </>
    );
}