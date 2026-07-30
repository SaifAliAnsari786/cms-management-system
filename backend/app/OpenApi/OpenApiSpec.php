<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="CMS Management System API",
 *     version="1.0.0",
 *     description="REST API documentation for CMS Management System"
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="role", type="string", example="editor"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Page",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="About Us"),
 *     @OA\Property(property="slug", type="string", example="about-us"),
 *     @OA\Property(property="content", type="string", example="<p>Page body</p>"),
 *     @OA\Property(property="status", type="string", example="published"),
 *     @OA\Property(property="cover_image", type="string", example="http://127.0.0.1:8000/storage/pages/cover.jpg", nullable=true),
 *     @OA\Property(property="published_at", type="string", format="date-time", example="2026-08-15 09:00:00", nullable=true),
 *     @OA\Property(property="created_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="updated_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Menu",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Main Navigation"),
 *     @OA\Property(property="page_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="parent_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="sort_order", type="integer", example=1),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="Role",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="editor"),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         @OA\Items(type="string")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Permission",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="posts.publish"),
 *     @OA\Property(property="guard_name", type="string", example="web")
 * )
 */
class OpenApiSpec
{
}