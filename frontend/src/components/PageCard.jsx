import { Link } from "react-router-dom";

export default function PageCard({ page }) {
    const content = (page.content || "")
        .replace(/<[^>]*>/g, "")
        .substring(0, 140);

    return (
        <div className="group bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col border border-gray-200 hover:-translate-y-1">

            <div className="aspect-video relative overflow-hidden">
                <img
                    src={
                        page.cover_image ||
                        "https://placehold.co/800x500?text=No+Image"
                    }
                    alt={page.title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                />

                <div className="absolute top-4 left-4">
                    <span className="bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full">
                        Published
                    </span>
                </div>
            </div>

            <div className="p-6 flex flex-col flex-1">

                <span className="text-xs font-semibold uppercase tracking-wider text-indigo-600 mb-2">
                    CMS Page
                </span>

                <h3 className="text-2xl font-semibold text-gray-900 mb-3 line-clamp-2">
                    {page.title}
                </h3>

                <p className="text-gray-600 leading-7 mb-6 line-clamp-3">
                    {content}...
                </p>

                <div className="mt-auto flex items-center justify-between border-t pt-4">

                    <span className="text-sm text-gray-500">
                        {new Date(page.published_at).toLocaleDateString()}
                    </span>

                    <Link
                        to={`/page/${page.id}`}
                        className="flex items-center gap-1 text-indigo-600 font-medium hover:text-indigo-800 transition-colors"
                    >
                        Read More

                        <span className="material-symbols-outlined text-lg">
                            chevron_right
                        </span>
                    </Link>

                </div>

            </div>

        </div>
    );
}
