import { NavLink } from "react-router-dom";

export default function Menu() {
    const menus = [
        {
            id: 1,
            title: "Home",
            url: "/",
        },
        // Dynamic menus will come from API
    ];

    return (
        <nav className="hidden md:flex items-center gap-8">
            {menus.map((menu) => (
                <NavLink
                    key={menu.id}
                    to={menu.url}
                    className={({ isActive }) =>
                        isActive
                            ? "text-indigo-600 font-semibold border-b-2 border-indigo-600 pb-1 transition-all"
                            : "text-gray-600 hover:text-indigo-600 pb-1 transition-all"
                    }
                >
                    {menu.title}
                </NavLink>
            ))}
        </nav>
    );
}