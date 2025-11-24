<?php

/**
 * @license Apache 2.0
 */
/**
 * @OA\Info(
 *     description="API documentation for afya365 electronic health records",
 *     version="1.0.0",
 *     title="Afya365",
 *     @OA\Contact(
 *         email="admin@crackit.co.ke",
 *         name="Ananda Douglas"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 */

 /**
 * @OA\Tag(
 *     name="About",
 *     description="Introduction to Afya365"
 * )
 */

/**
 *@OA\Schema(
 *  schema="About",
 *  @OA\Property(property="environment", type="string",title="Environment", example="dev"),
 *  @OA\Property(property="version", type="string",title="Application Version", example="v1"),
 *  @OA\Property(property="id", type="string",title="Application ID", example="a365"),
 *  @OA\Property(property="name", type="string",title="Application Name", example="Afya365"),
 * )
 */

/**
 * @OA\Get(path="/about",
 *   summary="System Info. ",
 *   tags={"About"},
 *   security={{}},
 *   @OA\Response(
 *     response=200,
 *     description="success",
 *      @OA\JsonContent(
 *          @OA\Property(property="data", type="array",@OA\Items(ref="#/components/schemas/About")),
 *          
 *      )
 *   ),
 * )
 */
/**
 * @license Apache 2.0
 */
/**
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 * )
 */
/**
 * @OA\OpenApi(
 *   security={
 *      {
 *          "bearerAuth":{}
 *      }
 *   }
 * )
 */