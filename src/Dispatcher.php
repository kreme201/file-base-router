<?php declare(strict_types=1);

namespace Kreme\FileBaseRouter;

final class Dispatcher
{
    public function __construct(
        private readonly string $base_path,
    ) {
    }

    public function dispatch(string $request_uri): false|string
    {
        if ('/' === $request_uri && file_exists($this->base_path . DIRECTORY_SEPARATOR . 'index.php')) {
            return $this->base_path . DIRECTORY_SEPARATOR . 'index.php';
        } elseif (file_exists($this->base_path . DIRECTORY_SEPARATOR . trim($request_uri, '/') . '.php')) {
            return $this->base_path . DIRECTORY_SEPARATOR . trim($request_uri, '/') . '.php';
        } elseif (file_exists($this->base_path . DIRECTORY_SEPARATOR . trim($request_uri,
                '/') . DIRECTORY_SEPARATOR . 'index.php')) {
            return $this->base_path . DIRECTORY_SEPARATOR . trim($request_uri, '/') . DIRECTORY_SEPARATOR . 'index.php';
        }

        $request_uri_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
        $template_path = $this->base_path;
        $uri_vars = [];

        while (!empty($request_uri_parts)) {
            $request_uri_part = array_shift($request_uri_parts);

            if (is_dir($template_path . DIRECTORY_SEPARATOR . $request_uri_part)) {
                $template_path .= DIRECTORY_SEPARATOR . $request_uri_part;
            } elseif (empty($request_uri_parts) && file_exists($template_path . DIRECTORY_SEPARATOR . $request_uri_part . '.php')) {
                $template_path .= DIRECTORY_SEPARATOR . $request_uri_part . '.php';
            } elseif (empty($request_uri_parts) && file_exists($template_path . DIRECTORY_SEPARATOR . $request_uri_part . DIRECTORY_SEPARATOR . 'index.php')) {
                $template_path .= DIRECTORY_SEPARATOR . $request_uri_part . DIRECTORY_SEPARATOR . 'index.php';
            } elseif (is_dir($template_path) && $template_path !== $this->base_path) {
                $folder_items = array_values(
                    array_filter(
                        scandir($template_path),
                        function ($item) {
                            return !(str_starts_with($item, '.') || str_starts_with($item, '__'));
                        },
                    ),
                );

                usort(
                    $folder_items,
                    function ($a, $b) {
                        if (str_starts_with($a, '[...') && !str_starts_with($b, '[...')) {
                            return 1;
                        } elseif (str_starts_with($b, '[...') && !str_starts_with($a, '[...')) {
                            return -1;
                        } elseif (str_starts_with($a, '[') && !str_starts_with($b, '[')) {
                            return 1;
                        } elseif (str_starts_with($b, '[') && !str_starts_with($a, '[')) {
                            return -1;
                        } elseif (str_ends_with($a, '.php') && !str_ends_with($b, '.php')) {
                            return 1;
                        } elseif (str_ends_with($b, '.php') && !str_ends_with($a, '.php')) {
                            return -1;
                        }

                        return strcasecmp($a, $b);
                    },
                );

                $found_folder_item = false;

                foreach ($folder_items as $folder_item) {
                    if (preg_match('/^\[\.\.\.(.*?)]/', $folder_item, $matches)) {
                        $template_path .= DIRECTORY_SEPARATOR . $folder_item;
                        $uri_vars[$matches[1]] = implode('/', [$request_uri_part, ...$request_uri_parts]);
                        $request_uri_parts = [];
                        $found_folder_item = true;
                        break;
                    } elseif (empty($request_uri_parts) && preg_match('/^\[(.*?)]\.php$/', $folder_item, $matches)) {
                        $template_path .= DIRECTORY_SEPARATOR . $folder_item;
                        $uri_vars[$matches[1]] = $request_uri_part;
                        $found_folder_item = true;
                        break;
                    } elseif (preg_match('/^\[(.*?)]$/', $folder_item, $matches)) {
                        $template_path .= DIRECTORY_SEPARATOR . $folder_item;
                        $uri_vars[$matches[1]] = $request_uri_part;
                        $found_folder_item = true;
                        break;
                    }
                }

                if (!$found_folder_item) {
                    $template_path = $this->base_path;
                }
            } else {
                $template_path = false;
                break;
            }
        }

        if (is_string($template_path)) {
            if ($template_path !== $this->base_path && is_dir($template_path) && file_exists($template_path . DIRECTORY_SEPARATOR . 'index.php')) {
                $template_path .= DIRECTORY_SEPARATOR . 'index.php';
            }

            if ($template_path !== $this->base_path && file_exists($template_path)) {
                $uri_vars = array_filter($uri_vars, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($uri_vars as $key => $value) {
                    $_GET[$key] = $value;
                }

                return $template_path;
            }
        }

        return false;
    }
}
