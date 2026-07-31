import { useParams } from "react-router-dom";

export default function PageDetails() {
    const { id } = useParams();

    return (
        <div>
            <h1>Page Details</h1>
            <p>Page ID: {id}</p>
        </div>
    );
}