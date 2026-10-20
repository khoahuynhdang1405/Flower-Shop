<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>

# UIUX Skill Rules

Khi thực hiện bất kỳ task nào liên quan đến giao diện web, UIUX, frontend design, animation, visual design, responsive layout, design system, accessibility hoặc visual asset, bắt buộc sử dụng các skill UIfrontend phù hợp bên dưới.

## 1. Bắt buộc cập nhật skill trước khi làm

Trước khi bắt đầu implement UI

1. Kiểm tra các skill hiện có trong môi trường.
2. Pullupdate repository skill mới nhất trước khi sử dụng.
3. Ưu tiên version mới nhất từ source repository, không dựa trên nội dung skill đã cache từ trước.
4. Nếu skill repository có thay đổi, đọc lại instructionskill liên quan trước khi implement.
5. Không tự tạo lại một skill đã có sẵn nếu skill hiện tại đã hỗ trợ task.

### Skill source chính

Hallmark `https://github.comNutlopehallmark`
NTCodingclaude-skillz `https://github.comNTCodingclaude-skillz`

Khi cần UIUX, hãy ưu tiên kiểm tra và sử dụng skill từ các repository trên trước khi code.

---

# 2. UIUX Design Skills

Có sẵn 17 skill

`ui-ux-pro-max` — hub chính
`design`
`design-system`
`ui-styling`
`frontend-design`
`web-design-guidelines`
`web-artifacts-builder`
`theme-factory`
`typography`
`design-audit`
`bencium-controlled-ux-designer`
`bencium-impact-designer`
`bencium-innovative-ux-designer`
`brutalist-skill`
`gpt-tasteskill`
`composition-patterns`
`canvas-design`

## Quy tắc

Không mặc định dùng tất cả skill cùng lúc.

Hãy xác định loại task trước rồi chọn skill phù hợp.

Ví dụ

Tổng thể UIUX → `ui-ux-pro-max`
Design system → `design-system`
Tailwindshadcn → `ui-styling`
Visual frontend → `frontend-design`
Accessibilityaudit → `web-design-guidelines`
Typography → `typography`
Visual audit → `design-audit`
Brutalist UI → `brutalist-skill`
Layoutcomposition → `composition-patterns`
Complex artifact → `web-artifacts-builder`
Theme → `theme-factory`

Nếu task cần nhiều khía cạnh, có thể kết hợp nhiều skill.

---

# 3. Frontend Engineering Animation Testing

Có sẵn 7 skill

`react-best-practices`
`react-view-transitions`
`react-native-skills`
`webapp-testing`
`gsap-animation`
`animejs-animation`
`dataviz`

Sử dụng khi task liên quan đến

React architecture
React performance
page transition
mobile UI
animation
interaction
data visualization
browser testing
visual regression testing

Không thêm animation chỉ để làm UI đẹp.

Animation phải phục vụ

hierarchy
feedback
transition
orientation
perceived performance

---

# 4. Image Brand Visual Asset Skills

Có sẵn 8 skill

`image-to-code-skill`
`imagegen-frontend-web`
`imagegen-frontend-mobile`
`image`
`brand`
`brandkit`
`banner-design`
`slides`

Sử dụng khi task có

reference image
screenshot
logo
brand guideline
banner
marketing visual
generated image
image-to-code
presentation visual

Nếu user cung cấp reference image, phải ưu tiên phân tích reference trước khi tự thiết kế.

Không tự thay đổi

logo
brand color
typography
spacing convention
visual identity

nếu project đã có design systembrand guideline.

---

# 5. Dataviz

`dataviz` là skill riêng cho data visualization.

Khi xây dựng

dashboard
analytics
statistics
charts
metrics
monitoring UI
financialdata interface

hãy kiểm tra `dataviz` trước khi tự thiết kế chart.

Ưu tiên

đúng loại chart
readability
hierarchy
responsive behavior
accessibility
tooltipinteraction hợp lý

Không sử dụng chart chỉ vì nó nhìn đẹp.

---

# 6. Quy trình bắt buộc trước khi code UI

Thực hiện theo thứ tự

### Step 1 — Pull Update Skills

Kiểm tra repository và pull phiên bản mới nhất.

### Step 2 — Discover

Xác định

task thuộc loại UI nào
framework hiện tại
component library
styling system
existing design system
existing components
existing brand assets

### Step 3 — Select Skills

Chọn skill phù hợp từ danh sách trên.

Không load toàn bộ skill nếu không cần thiết.

### Step 4 — Read Skill Instructions

Đọc instruction của skill trước khi implementation.

Nếu nhiều skill được chọn, xác định rõ skill nào chịu trách nhiệm cho

UX
visual design
component implementation
animation
accessibility
testing

### Step 5 — Inspect Existing Project

Trước khi tạo component mới

tìm component tương tự
tìm design token
tìm màu sắc
tìm typography
tìm spacing
tìm layout pattern
tìm reusable component
tìm existing animation
tìm existing utility

Không tạo duplicate component nếu project đã có implementation phù hợp.

### Step 6 — Plan

Tạo implementation plan ngắn.

Plan phải mô tả

layout
component structure
responsive behavior
interaction
animation nếu cần
accessibility
datastate
reusable components

### Step 7 — Implement

Implement theo skill instructions và architecture hiện tại của project.

Ưu tiên

reusable components
small components
clear responsibility
existing project conventions
responsive design
accessibility
performance

Không hardcode dữ liệu hoặc style không cần thiết.

### Step 8 — Audit

Sau khi implement

kiểm tra visual consistency
responsive
accessibility
interaction
overflow
loadingemptyerror states
keyboard navigation
contrast
animation
performance

Nếu phù hợp, sử dụng

`web-design-guidelines`
`design-audit`
`webapp-testing`

### Step 9 — Final Polish

Chỉ sau khi UI hoạt động đúng mới thực hiện visual polish.

Không dùng polish để che architecturecomponent structure kém.

---

# 7. Anti-Generic UI

Không tạo UI generic chỉ bằng cách

gradient ngẫu nhiên
glassmorphism mặc định
rounded card hàng loạt
shadow quá mạnh
purpleblue gradient mặc định
font mặc định không phù hợp
icon dùng không nhất quán
animation dư thừa

Mọi visual decision phải có lý do liên quan đến

product
brand
information hierarchy
user flow
usability

Nếu project đã có visual identity, ưu tiên consistency hơn việc tạo một design hoàn toàn mới.

---

# 8. Reference First

Nếu task có screenshotreferencedesign

1. Phân tích reference.
2. Xác định layout.
3. Xác định typography.
4. Xác định spacing.
5. Xác định colortoken.
6. Xác định component structure.
7. Xác định interaction.
8. Sau đó mới code.

Sử dụng `image-to-code-skill` khi reference cần được chuyển thành implementation.

Không chỉ nhìn screenshot rồi tự đoán toàn bộ design.

---

# 9. Definition of Done

Một UI task chỉ được xem là hoàn thành khi

UI hoạt động đúng.
Responsive.
Không phá existing design system.
Không duplicate component không cần thiết.
Accessibility cơ bản được đảm bảo.
Loadingemptyerror state được xử lý nếu cần.
Interaction hoạt động đúng.
Animation không gây khó chịu hoặc ảnh hưởng usability.
Không có console error liên quan.
Không có obvious overflowlayout bug.
Đã kiểm tra visual consistency.
Đã sử dụng skill phù hợp.
Skill đã được updatepull trước khi bắt đầu task.

## Important

Không bắt đầu code UI ngay lập tức.

Luôn

`Pull latest skills → Discover → Select skills → Read skills → Inspect project → Plan → Implement → Audit → Polish`

Nếu task không liên quan đến UIfrontendvisual thì không cần áp dụng toàn bộ workflow này.

## Tài liệu dự án Lá & Hoa

Dự án: website thương mại điện tử tiệm hoa tươi **Lá & Hoa** (Laravel 12, MySQL 8.0), gồm Storefront cho khách và trang Admin (`/admin`) cho chủ tiệm + nhân viên/thợ cắm.

Trước khi code bất kỳ tính năng, migration, model hay giao diện nào, đọc các tài liệu liên quan:

- `docs/database/schema.dbml` — schema 17 bảng (nguồn chuẩn cho migration và model).
- `docs/database/database-spec.md` — ý nghĩa nghiệp vụ và đặc tả từng cột, quan hệ, lưu ý Laravel.
- `docs/designs/README.md` — danh sách 20 màn hình, bảng DB liên quan, design language (màu, font, thành phần), ánh xạ trạng thái đơn/thanh toán sang nhãn tiếng Việt.
- `docs/designs/storefront/*.png` và `docs/designs/admin/*.png` — ảnh thiết kế. Khi làm một trang, mở đúng ảnh của trang đó và bám sát bố cục, nội dung, giọng văn.
- `docs/design-vs-db-gaps.md` — những gì thiết kế có mà DB chưa có, và các chỗ thiết kế chưa nhất quán.

Quy tắc:

- Không tự ý thêm bảng/cột ngoài `schema.dbml`. Nếu tính năng cần dữ liệu nằm trong `design-vs-db-gaps.md`, dừng lại và hỏi trước.
- Gặp số liệu mâu thuẫn giữa các màn hình (giá, phí giao, mã đơn, hotline...), không tự chọn — hỏi lại.
- Hai guard riêng: `web` (bảng `users`) cho khách, `admin` (bảng `admins`) cho quản trị. Phân quyền qua `roles` / `permissions` / `role_has_permissions` (slug vai trò: `admin`, `staff`).
- `order_items` lưu snapshot (tên, size, đơn giá) tại thời điểm đặt; `item_type` = `flower` | `addon`.
- Toàn bộ nội dung giao diện bằng tiếng Việt, giọng văn mềm mại, xưng "Lá"/"tiệm", gọi khách là "bạn". Tiền định dạng `680.000 đ`, ngày `dd/mm/yyyy`.
