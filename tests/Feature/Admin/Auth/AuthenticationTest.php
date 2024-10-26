<?php

namespace Tests\Feature\Admin\Auth;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_admins_can_authenticate_using_the_login_screen(): void
    {
        // テスト用の管理者ユーザーを作成（パスワードをハッシュ化）
        // $admin = Admin::factory()->create([
        // 'password' => Hash::make('nagoyameshi'),
        // ]);
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // ログインリクエストを送信
        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'nagoyameshi', // 正しいパスワード
        ]);

        // 認証が成功していることを確認
        $this->assertTrue(Auth::guard('admin')->check());

        // セッションにエラーがないことを確認
        $response->assertSessionHasNoErrors();

        // ダッシュボードへのリダイレクトを確認
        $response->assertRedirect(config('constants.ADMIN_HOME'));
    }

    public function test_admins_can_not_authenticate_with_invalid_password(): void
    {
        // テスト用の管理者ユーザーを作成（パスワードをハッシュ化）
        /*
        $admin = Admin::factory()->create([
            'password' => Hash::make('nagoyameshi'),
        ]);
        */
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 誤ったパスワードでログインリクエストを送信
        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password', // 間違ったパスワード
        ]);

        // 認証されていないことを確認
        $this->assertGuest();
    }

    public function test_admins_can_logout(): void
    {
        // テスト用の管理者ユーザーを作成（パスワードをハッシュ化）
        /*
        $admin = Admin::factory()->create([
            'password' => Hash::make('nagoyameshi'),
        ]);
        */
        $admin = new Admin();
        $admin->email = 'admin@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 管理者としてログインし、ログアウトリクエストを送信
        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        // 認証されていない状態であることを確認
        $this->assertGuest();

        // リダイレクトが正しく行われたか確認
        $response->assertRedirect(route('/', absolute: false));
    }
}
