@extends('layouts.guest')

@section('title', 'Publicar Artículo Científico — Revista A tu lado')

@section('content')
<div style="background: #F8FAF9; padding: 3rem 1.25rem 5rem;">
  <div class="container-narrow" style="max-width: 860px; margin: 0 auto;">

    <!-- Breadcrumb & Back Link -->
    <div style="margin-bottom: 1.25rem; font-family: var(--font-mono); font-size: 0.82rem;">
      <a href="{{ route('revista.index') }}" style="color: #2E5D4B; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
        <i class="fa-solid fa-arrow-left"></i> <span>Volver a la Revista</span>
      </a>
    </div>

    <!-- Header Card -->
    <div style="background: linear-gradient(135deg, #111A14 0%, #1E2A22 100%) !important; color: #FFFFFF !important; border-radius: 20px; padding: 2rem 2.25rem; margin-bottom: 2rem; border: 1px solid rgba(255,255,255,0.08); box-shadow: var(--shadow-md);">
      <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); padding: 0.25rem 0.75rem; border-radius: 9999px; font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; color: #A8E6C0; margin-bottom: 0.75rem;">
        <i class="fa-solid fa-microscope"></i>
        <span>Portal de Publicación Científica & Psicoeducación</span>
      </div>
      <h1 style="color: #FFFFFF !important; font-size: clamp(1.6rem, 3.5vw, 2.2rem); margin-bottom: 0.4rem; line-height: 1.2;">
        Publicar Artículo o Investigación
      </h1>
      <p style="color: #C8DDD1 !important; font-size: 0.92rem; line-height: 1.5; margin: 0;">
        Comparte evidencia clínica, protocolos de regulación emocional, revisiones científicas o reflexiones para la comunidad.
      </p>
    </div>

    <!-- Main Creation Form Card -->
    <div class="card" style="border-top: 5px solid #2E5D4B; border-radius: 16px; box-shadow: var(--shadow-sm); overflow: hidden;">
      <div class="card-body" style="padding: 2.5rem 2rem;">

        <form method="POST" action="{{ route('revista.store') }}" id="publishArticleForm" enctype="multipart/form-data">
          @csrf

          <!-- SECCIÓN 1: DATOS PRINCIPALES Y ÁREA TEMÁTICA -->
          <div style="margin-bottom: 2rem;">
            <div style="font-family: var(--font-mono); font-size: 0.76rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #E2ECE6; padding-bottom: 0.5rem;">
              <i class="fa-solid fa-heading"></i>
              <span>1. Título & Área Temática Principal</span>
            </div>

            <!-- Título -->
            <div class="form-group" style="margin-bottom: 1.35rem;">
              <label for="title" class="form-label" style="font-weight: 700; font-size: 0.9rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                Título del Artículo o Investigación <span style="color: #C0392B;">*</span>
              </label>
              <input 
                type="text" 
                name="title" 
                id="title" 
                class="form-control @error('title') is-invalid @enderror" 
                value="{{ old('title') }}" 
                placeholder="Ej. Regulación emocional en crisis: El papel neurobiológico del nervio vago y DBT" 
                required
                style="font-size: 0.96rem; padding: 0.8rem 1rem; border-radius: 10px;"
              >
              @error('title')
                <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
              @enderror
            </div>

            <!-- Área Temática & Tono Visual -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
              <div>
                <label for="topic_area_id" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Área Temática Principal <span style="color: #C0392B;">*</span>
                </label>
                <select name="topic_area_id" id="topic_area_id" class="form-control @error('topic_area_id') is-invalid @enderror" required style="padding: 0.75rem 1rem; border-radius: 10px;">
                  <option value="">Selecciona un área temática...</option>
                  @foreach($topicAreas as $area)
                    <option value="{{ $area->id }}" {{ old('topic_area_id') == $area->id ? 'selected' : '' }}>
                      {{ $area->name }}
                    </option>
                  @endforeach
                </select>
                @error('topic_area_id')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label for="visual_theme" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Tono Visual / Paleta
                </label>
                <select name="visual_theme" id="visual_theme" class="form-control" style="padding: 0.75rem 1rem; border-radius: 10px;">
                  <option value="salvia" {{ old('visual_theme') == 'salvia' ? 'selected' : '' }}>Salvia (Clínico / Calma)</option>
                  <option value="lav" {{ old('visual_theme') == 'lav' ? 'selected' : '' }}>Violeta (DBT / Mente Consciente)</option>
                  <option value="sky" {{ old('visual_theme') == 'sky' ? 'selected' : '' }}>Azul Cielo (Fisiología / Ansiedad)</option>
                  <option value="terra" {{ old('visual_theme') == 'terra' ? 'selected' : '' }}>Terracota (Emociones / Duelo)</option>
                  <option value="amber" {{ old('visual_theme') == 'amber' ? 'selected' : '' }}>Ámbar (Hábitos / Esperanza)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- SECCIÓN 2: AUTORÍA, RIGOR & AUDIENCIA -->
          <div style="margin-bottom: 2rem;">
            <div style="font-family: var(--font-mono); font-size: 0.76rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #E2ECE6; padding-bottom: 0.5rem;">
              <i class="fa-solid fa-user-doctor"></i>
              <span>2. Autoría, Enfoque & Audiencia</span>
            </div>

            <!-- Autor & Credenciales -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 1.35rem;">
              <div>
                <label for="author_name" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Nombre Público del Autor(a) <span style="color: #C0392B;">*</span>
                </label>
                <input 
                  type="text" 
                  name="author_name" 
                  id="author_name" 
                  class="form-control @error('author_name') is-invalid @enderror" 
                  value="{{ old('author_name', auth()->user()?->name ?? '') }}" 
                  placeholder="Ej. Dra. Mariana Valenzuela" 
                  required
                  style="border-radius: 10px;"
                >
                @error('author_name')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label for="author_credentials" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Credenciales / Especialidad <span style="color: #C0392B;">*</span>
                </label>
                <input 
                  type="text" 
                  name="author_credentials" 
                  id="author_credentials" 
                  class="form-control @error('author_credentials') is-invalid @enderror" 
                  value="{{ old('author_credentials', auth()->user()?->professional_title ?? 'Psicólogo Clínico · Terapeuta DBT') }}" 
                  placeholder="Ej. Psicóloga Clínica · Terapeuta DBT Certificada" 
                  required
                  style="border-radius: 10px;"
                >
                @error('author_credentials')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <!-- Tipo de Publicación & Audiencia Objetivo -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
              <div>
                <label for="publication_type" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Tipo de Publicación / Rigor <span style="color: #C0392B;">*</span>
                </label>
                <select name="publication_type" id="publication_type" class="form-control @error('publication_type') is-invalid @enderror" required style="padding: 0.75rem 1rem; border-radius: 10px;">
                  <option value="divulgacion" {{ old('publication_type') == 'divulgacion' ? 'selected' : '' }}>Divulgación Basada en Evidencia</option>
                  <option value="revision" {{ old('publication_type') == 'revision' ? 'selected' : '' }}>Revisión Científica / Literatura</option>
                  <option value="caso_estudio" {{ old('publication_type') == 'caso_estudio' ? 'selected' : '' }}>Caso de Estudio / Reflexión Clínica</option>
                  <option value="guia" {{ old('publication_type') == 'guia' ? 'selected' : '' }}>Guía Clínica / Protocolo Paso a Paso</option>
                </select>
                @error('publication_type')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>

              <div>
                <label for="target_audience" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                  Audiencia Recomendada <span style="color: #C0392B;">*</span>
                </label>
                <select name="target_audience" id="target_audience" class="form-control @error('target_audience') is-invalid @enderror" required style="padding: 0.75rem 1rem; border-radius: 10px;">
                  <option value="general" {{ old('target_audience') == 'general' ? 'selected' : '' }}>Público General (Comprensible y accesible)</option>
                  <option value="estudiantes" {{ old('target_audience') == 'estudiantes' ? 'selected' : '' }}>Estudiantes de Psicología / Salud</option>
                  <option value="profesionales" {{ old('target_audience') == 'profesionales' ? 'selected' : '' }}>Profesionales & Terapeutas Clínicos</option>
                </select>
                @error('target_audience')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- SECCIÓN 3: CONTENIDO & EXTRACTO CIENTÍFICO -->
          <div style="margin-bottom: 2rem;">
            <div style="font-family: var(--font-mono); font-size: 0.76rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #E2ECE6; padding-bottom: 0.5rem;">
              <i class="fa-solid fa-align-left"></i>
              <span>3. Resumen & Cuerpo Completo</span>
            </div>

            <!-- Resumen / Abstract -->
            <div class="form-group" style="margin-bottom: 1.35rem;">
              <label for="summary" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                Resumen Científico / Extracto Breve <span style="color: #C0392B;">*</span>
              </label>
              <textarea 
                name="summary" 
                id="summary" 
                rows="3" 
                class="form-control @error('summary') is-invalid @enderror" 
                placeholder="Sintetiza la hipótesis, el hallazgo clínico o la premisa central del artículo en 2-3 oraciones..." 
                required
                style="resize: vertical; font-size: 0.92rem; border-radius: 10px;"
              >{{ old('summary') }}</textarea>
              @error('summary')
                <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
              @enderror
            </div>

            <!-- Cuerpo Completo -->
            <div class="form-group" style="margin-bottom: 1.35rem;">
              <label for="content" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: block;">
                Cuerpo del Artículo (Markdown o Texto Enriquecido) <span style="color: #C0392B;">*</span>
              </label>
              <textarea 
                name="content" 
                id="content" 
                rows="14" 
                class="form-control @error('content') is-invalid @enderror" 
                placeholder="Desarrolla el artículo con subtítulos (### Subtítulo), explicaciones comprensibles y técnicas aplicadas..." 
                required
                style="resize: vertical; font-size: 0.94rem; line-height: 1.7; border-radius: 10px; font-family: inherit;"
              >{{ old('content') }}</textarea>
              @error('content')
                <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- SECCIÓN 4: RECURSOS COMPLEMENTARIOS & DEBATE -->
          <div style="margin-bottom: 2rem;">
            <div style="font-family: var(--font-mono); font-size: 0.76rem; text-transform: uppercase; color: #2E5D4B; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 1rem; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #E2ECE6; padding-bottom: 0.5rem;">
              <i class="fa-solid fa-book-bookmark"></i>
              <span>4. Metadatos, Referencias & Debate</span>
            </div>

            <!-- Imagen de Portada (Subida de Archivo o URL) & Tiempo de Lectura -->
            <div style="background: #F8FAF9; border: 1.5px dashed #C8DDD1; border-radius: 14px; padding: 1.5rem; margin-bottom: 1.5rem;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 6px;">
                <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: #1A2620; margin: 0; display: flex; align-items: center; gap: 8px;">
                  <i class="fa-solid fa-image" style="color: #2E5D4B; font-size: 1.1rem;"></i>
                  <span>Imagen de Portada del Artículo (Opcional)</span>
                </label>
                <span style="font-size: 0.76rem; color: #6A8275; font-family: var(--font-mono);">Formatos: JPG, PNG, WEBP (Máx. 5 MB)</span>
              </div>

              <!-- Upload Drag & Drop Box -->
              <div id="coverDropzone" style="background: #FFFFFF; border: 1px solid #DCE8E0; border-radius: 12px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s ease; margin-bottom: 1rem;" onclick="document.getElementById('cover_image').click();">
                <input 
                  type="file" 
                  name="cover_image" 
                  id="cover_image" 
                  accept="image/png,image/jpeg,image/jpg,image/webp,image/gif"
                  style="display: none;"
                  onchange="handleCoverImageChange(this)"
                >
                <div id="coverUploadPlaceholder">
                  <div style="width: 50px; height: 50px; border-radius: 50%; background: #EBF5EF; color: #2E5D4B; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 0.6rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                  </div>
                  <div style="font-size: 0.92rem; font-weight: 700; color: #1A2620;">Haz clic aquí para seleccionar una imagen de portada</div>
                  <div style="font-size: 0.8rem; color: #6A8275; margin-top: 0.25rem;">O arrastra y suelta tu archivo en esta área</div>
                </div>

                <!-- Preview Area -->
                <div id="coverPreviewContainer" style="display: none; position: relative;">
                  <img id="coverPreviewImg" src="" alt="Vista previa de portada" style="max-height: 240px; width: 100%; object-fit: cover; border-radius: 10px; border: 1px solid #C8DDD1;">
                  <div style="margin-top: 0.6rem; display: flex; justify-content: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); document.getElementById('cover_image').click();" style="border-radius: 8px;">
                      <i class="fa-solid fa-rotate"></i> Cambiar imagen
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); removeCoverImage();" style="border-radius: 8px; color: #C0392B;">
                      <i class="fa-solid fa-trash-can"></i> Quitar
                    </button>
                  </div>
                </div>
              </div>
              @error('cover_image')
                <div style="color: #C0392B; font-size: 0.8rem; margin-bottom: 0.75rem;">{{ $message }}</div>
              @enderror

              <!-- Alternative: Image URL Input -->
              <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; align-items: flex-start;">
                <div>
                  <label for="cover_image_path" class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #556860; margin-bottom: 0.3rem; display: block;">
                    O ingresa una URL web de imagen externa:
                  </label>
                  <input 
                    type="text" 
                    name="cover_image_path" 
                    id="cover_image_path" 
                    class="form-control @error('cover_image_path') is-invalid @enderror" 
                    value="{{ old('cover_image_path') }}" 
                    placeholder="https://ejemplo.com/imagen.jpg" 
                    style="border-radius: 10px; font-size: 0.86rem;"
                    oninput="handleCoverUrlChange(this.value)"
                  >
                  @error('cover_image_path')
                    <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                  @enderror
                </div>

                <div>
                  <label for="reading_time_min" class="form-label" style="font-weight: 600; font-size: 0.82rem; color: #556860; margin-bottom: 0.3rem; display: block;">
                    Tiempo de lectura (min):
                  </label>
                  <input 
                    type="number" 
                    name="reading_time_min" 
                    id="reading_time_min" 
                    min="1" 
                    max="120"
                    class="form-control @error('reading_time_min') is-invalid @enderror" 
                    value="{{ old('reading_time_min', 4) }}" 
                    style="border-radius: 10px; font-size: 0.86rem;"
                  >
                  @error('reading_time_min')
                    <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <!-- Pregunta de Debate (Discussion Prompt) -->
            <div class="form-group" style="margin-bottom: 1.35rem;">
              <label for="discussion_prompt" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1A2620; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-comments" style="color: #2E5D4B;"></i>
                <span>Pregunta Abierta para el Debate Comunitario (Opcional)</span>
              </label>
              <input 
                type="text" 
                name="discussion_prompt" 
                id="discussion_prompt" 
                class="form-control @error('discussion_prompt') is-invalid @enderror" 
                value="{{ old('discussion_prompt') }}" 
                placeholder="Ej. ¿Qué estrategia te ha resultado más eficaz para pausar antes de reaccionar impulsivamente?" 
                style="border-radius: 10px;"
              >
              @error('discussion_prompt')
                <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
              @enderror
            </div>

            <!-- Citas Bibliográficas (APA) -->
            <div class="form-group" style="margin-bottom: 1.35rem;">
              <label for="references" class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #2E5D4B; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-book-journal-whills"></i>
                <span>Referencias Bibliográficas (Formato APA recomendado)</span>
              </label>
              <textarea 
                name="references" 
                id="references" 
                rows="3" 
                class="form-control" 
                placeholder="Ej. Linehan, M. M. (2015). DBT Skills Training Manual (2nd ed.). Guilford Publications."
                style="font-size: 0.86rem; border-radius: 10px;"
              >{{ old('references') }}</textarea>
            </div>

            <!-- Permitir Comentarios Toggle -->
            <div style="background: #F4F8F5; border: 1px solid #DCE8E0; border-radius: 12px; padding: 0.9rem 1.15rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.35rem;">
              <div>
                <div style="font-weight: 700; font-size: 0.88rem; color: #1A2620;">Habilitar Foro & Comentarios</div>
                <div style="font-size: 0.78rem; color: #6A8275;">Permite a los usuarios y profesionales debatir reflexiones al pie del artículo.</div>
              </div>
              <label style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0; cursor: pointer;">
                <input type="checkbox" name="allow_comments" value="1" {{ old('allow_comments', '1') ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                <span class="custom-switch-slider"></span>
              </label>
            </div>
          </div>

          <!-- SECCIÓN 5: DESLINDE CLÍNICO & CONFIRMACIÓN -->
          <div style="margin-bottom: 2rem; background: #FFFDF5; border: 1px solid #F9E79F; border-radius: 14px; padding: 1.25rem 1.4rem;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
              <i class="fa-solid fa-shield-halved" style="color: #D4AC0D; font-size: 1.25rem; margin-top: 2px;"></i>
              <div>
                <h4 style="margin: 0 0 0.35rem 0; font-size: 0.92rem; color: #7D6608; font-weight: 700;">Deslinde Ético & Psicoeducativo</h4>
                <p style="font-size: 0.82rem; color: #7D6608; line-height: 1.5; margin-bottom: 0.75rem;">
                  Declaro que el contenido compartido está respaldado por evidencia científica y tiene fines estrictamente psicoeducativos e informativos. No sustituye la psicoterapia individual ni la atención médica o psiquiátrica de urgencia.
                </p>
                <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.86rem; color: #1A2620; cursor: pointer;">
                  <input type="checkbox" name="is_disclaimer_accepted" value="1" {{ old('is_disclaimer_accepted') ? 'checked' : '' }} required>
                  <span>Acepto el deslinde de responsabilidad y los lineamientos éticos de publicación <span style="color: #C0392B;">*</span></span>
                </label>
                @error('is_disclaimer_accepted')
                  <div style="color: #C0392B; font-size: 0.8rem; margin-top: 0.3rem;">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <!-- Botones de Acción -->
          <div style="display: flex; justify-content: flex-end; gap: 0.85rem; align-items: center; padding-top: 1rem; border-top: 1px solid #E2ECE6;">
            <a href="{{ route('revista.index') }}" class="btn btn-secondary" style="border-radius: 10px;">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg" style="gap: 8px; padding: 0.85rem 2rem; border-radius: 10px; font-weight: 700;">
              <i class="fa-solid fa-paper-plane"></i>
              <span>Publicar en la Revista</span>
            </button>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

<script>
function handleCoverImageChange(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('coverPreviewImg').src = e.target.result;
      document.getElementById('coverPreviewContainer').style.display = 'block';
      document.getElementById('coverUploadPlaceholder').style.display = 'none';
      document.getElementById('cover_image_path').value = '';
    };
    reader.readAsDataURL(file);
  }
}

function removeCoverImage() {
  const input = document.getElementById('cover_image');
  input.value = '';
  document.getElementById('coverPreviewImg').src = '';
  document.getElementById('coverPreviewContainer').style.display = 'none';
  document.getElementById('coverUploadPlaceholder').style.display = 'block';
}

function handleCoverUrlChange(url) {
  if (url && (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/storage/'))) {
    document.getElementById('coverPreviewImg').src = url;
    document.getElementById('coverPreviewContainer').style.display = 'block';
    document.getElementById('coverUploadPlaceholder').style.display = 'none';
  } else if (!document.getElementById('cover_image').value) {
    document.getElementById('coverPreviewContainer').style.display = 'none';
    document.getElementById('coverUploadPlaceholder').style.display = 'block';
  }
}

// Drag & drop support on cover dropzone
const dropzone = document.getElementById('coverDropzone');
if (dropzone) {
  ['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.style.borderColor = '#2E5D4B';
      dropzone.style.background = '#F4F8F5';
    }, false);
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.style.borderColor = '#DCE8E0';
      dropzone.style.background = '#FFFFFF';
    }, false);
  });

  dropzone.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    if (files && files.length > 0) {
      const fileInput = document.getElementById('cover_image');
      fileInput.files = files;
      handleCoverImageChange(fileInput);
    }
  }, false);
}
</script>
@endsection
