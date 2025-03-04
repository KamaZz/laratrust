<?php echo '<?php' ?>


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LaratrustSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create table for storing groups
        Schema::create('{{ $laratrust['tables']['groups'] }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Create table for storing permissions
        Schema::create('{{ $laratrust['tables']['permissions'] }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

@if ($laratrust['teams']['enabled'])
        // Create table for storing teams
        Schema::create('{{ $laratrust['tables']['teams'] }}', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

@endif
        // Create table for associating groups to users and teams (Many To Many Polymorphic)
        Schema::create('{{ $laratrust['tables']['group_user'] }}', function (Blueprint $table) {
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['group'] }}');
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['user'] }}');
            $table->string('user_type');
@if ($laratrust['teams']['enabled'])
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();
@endif

            $table->foreign('{{ $laratrust['foreign_keys']['group'] }}')->references('id')->on('{{ $laratrust['tables']['groups'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
@if ($laratrust['teams']['enabled'])
            $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['group'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);
@else

            $table->primary(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['group'] }}', 'user_type']);
@endif
        });

        // Create table for associating permissions to users (Many To Many Polymorphic)
        Schema::create('{{ $laratrust['tables']['permission_user'] }}', function (Blueprint $table) {
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['permission'] }}');
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['user'] }}');
            $table->string('user_type');
@if ($laratrust['teams']['enabled'])
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['team'] }}')->nullable();
@endif

            $table->foreign('{{ $laratrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $laratrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
@if ($laratrust['teams']['enabled'])
            $table->foreign('{{ $laratrust['foreign_keys']['team'] }}')->references('id')->on('{{ $laratrust['tables']['teams'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->unique(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['permission'] }}', 'user_type', '{{ $laratrust['foreign_keys']['team'] }}']);
@else

            $table->primary(['{{ $laratrust['foreign_keys']['user'] }}', '{{ $laratrust['foreign_keys']['permission'] }}', 'user_type']);
@endif
        });

        // Create table for associating permissions to groups (Many-to-Many)
        Schema::create('{{ $laratrust['tables']['permission_group'] }}', function (Blueprint $table) {
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['permission'] }}');
            $table->unsignedBigInteger('{{ $laratrust['foreign_keys']['group'] }}');

            $table->foreign('{{ $laratrust['foreign_keys']['permission'] }}')->references('id')->on('{{ $laratrust['tables']['permissions'] }}')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('{{ $laratrust['foreign_keys']['group'] }}')->references('id')->on('{{ $laratrust['tables']['groups'] }}')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['{{ $laratrust['foreign_keys']['permission'] }}', '{{ $laratrust['foreign_keys']['group'] }}']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('{{ $laratrust['tables']['permission_user'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['permission_group'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['permissions'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['group_user'] }}');
        Schema::dropIfExists('{{ $laratrust['tables']['groups'] }}');
@if ($laratrust['teams']['enabled'])
        Schema::dropIfExists('{{ $laratrust['tables']['teams'] }}');
@endif
    }
}
