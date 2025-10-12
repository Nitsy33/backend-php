INSERT INTO company(name) VALUES ('Acme S.A.') ON CONFLICT DO NOTHING;

INSERT INTO user_account(name,email) VALUES
  ('María','maria@acme.com'),
  ('Juan','juan@acme.com'),
  ('Ana','ana@acme.com')
ON CONFLICT DO NOTHING;

-- Ajusta ids según existan
-- INSERT areas, members, etc.
